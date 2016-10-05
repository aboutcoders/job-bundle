<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\Dbal;

use Abc\Bundle\JobBundle\Doctrine\Job;
use Abc\Bundle\JobBundle\Doctrine\ScheduleManager;
use Abc\Bundle\JobBundle\Entity\JobManager as BaseManager;
use Abc\Bundle\JobBundle\Entity\Schedule;
use Abc\Bundle\JobBundle\Job\Status;
use Abc\Bundle\JobBundle\Model\JobInterface;
use Abc\Bundle\JobBundle\Model\ScheduleInterface;
use Abc\Bundle\JobBundle\Serializer\Job\SerializationHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\UuidGenerator;
use PDO;


/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class JobManager extends BaseManager
{
    /**
     * @var UuidGenerator
     */
    private $uuidGenerator;

    public function __construct(EntityManager $em, $class, ScheduleManager $scheduleManager, SerializationHelper $serializationHelper)
    {
        parent::__construct($em, $class, $scheduleManager, $serializationHelper);

        $this->uuidGenerator = new UuidGenerator();
    }


    /**
     * {@inheritdoc}
     */
    public function findByTicket($ticket)
    {
        $connection = $this->em->getConnection();

        $query = <<<SQL
SELECT abc_job.*, abc_job_schedule.id as schedule_id, abc_job_schedule.type as schedule_type, abc_job_schedule.expression as schedule_expression, abc_job_schedule.scheduled_at as schedule_scheduled_at, abc_job_schedule.is_active as schedule_is_active, abc_job_schedule.created_at as schedule_created_at, abc_job_schedule.updated_at as schedule_updated_at
FROM abc_job
LEFT JOIN abc_job_schedule ON abc_job.ticket = abc_job_schedule.job_ticket 
WHERE ticket = 
SQL;
        $query .= $connection->quote((string)$ticket);
        $rs = $connection->executeQuery($query)->fetch(PDO::FETCH_ASSOC);

        if (empty($rs)) {
            return null;
        }

        $job = $this->create();
        if (!$job instanceof \Abc\Bundle\JobBundle\Doctrine\Job) {
            throw new \RuntimeException(sprintf('The job class "%s" must be a subclass of \Abc\Bundle\JobBundle\Doctrine\Job', $this->getClass()));
        }

        $job->setTicket($rs['ticket']);
        $job->setType($rs['type']);
        $job->setSerializedParameters($rs['serialized_parameters']);
        $job->setSerializedResponse($rs['serialized_response']);
        $job->setProcessingTime($rs['processing_time']);
        $job->setCreatedAt(new \DateTime($rs['created_at']));
        if (null != $rs['terminated_at']) {
            $job->setTerminatedAt(new \DateTime($rs['terminated_at']));
        }
        if (null != $rs['status']) {
            $job->setStatus(new Status($rs['status']));
        }

        if (null != $rs['schedule_id']) {
            $schedule = new Schedule();
            $schedule->setId($rs['schedule_id']);
            $schedule->setJob($job);
            $schedule->setType($rs['schedule_type']);
            $schedule->setExpression($rs['schedule_expression']);
            $schedule->setScheduledAt($rs['schedule_scheduled_at']);
            $schedule->setIsActive($rs['schedule_is_active']);
            $schedule->setUpdatedAt($rs['schedule_updated_at'] == null ? null : $this->createDateTime($rs['schedule_updated_at']));
            $schedule->setCreatedAt($rs['schedule_created_at'] == null ? null : $this->createDateTime($rs['schedule_created_at']));
        }

        return $job;
    }

    /**
     * {@inheritdoc}
     */
    public function save(JobInterface $job)
    {
        if (!$job instanceof \Abc\Bundle\JobBundle\Doctrine\Job) {
            throw new \InvalidArgumentException(sprintf('The job class "%s" must be a subclass of \Abc\Bundle\JobBundle\Doctrine\Job', $this->getClass()));
        }

        try {
            $this->getConnection()->beginTransaction();

            if (null != $job->getTicket() && $this->jobExists($job->getTicket())) {
                $this->getConnection()->executeQuery($this->buildQueryUpdateJob($job));
            } else {
                $job->setTicket($this->generateUuid($job));

                $this->getConnection()->executeQuery($this->buildQueryInsertJob($job));
            }

            if (null != $job->getTicket() && !$job->hasSchedules()) {
                $this->getConnection()->executeQuery($this->buildQueryDeleteSchedulesByJobTicket($job->getTicket()));
            } else {
                $schedulePKs = null == $job->getTicket() ? [] : $this->getConnection()->executeQuery($this->buildQuerySelectSchedulePKs($job->getTicket()))->fetch(PDO::FETCH_ASSOC);
                foreach ($job->getSchedules() as $schedule) {
                    if (!empty($schedulePKs)) {
                        $this->getConnection()->executeQuery($this->buildQueryUpdateSchedule(array_pop($schedulePKs), $schedule));
                    } else {
                        $this->getConnection()->executeQuery($this->buildQueryInsertSchedule($schedule));
                    }
                }
                if (count($schedulePKs) > 0) {
                    $this->getConnection()->executeQuery($this->buildQueryDeleteSchedulesByPks($schedulePKs));
                }
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $ticket
     * @return bool Whether a job with the given ticket exists
     */
    protected function jobExists($ticket)
    {
        $query = 'SELECT COUNT(*) FROM abc_job WHERE ticket=' . $this->em->getConnection()->quote($ticket);

        return (int)$this->getConnection()->executeQuery($query)->fetch(PDO::FETCH_COLUMN) > 0;
    }

    /**
     * @param \DateTime|null $date
     * @return string
     */
    protected function convertDateToSql(\DateTime $date = null)
    {
        return $date == null ? 'NULL' : sprintf('FROM_UNIXTIME(%d)', $date->format('U'));
    }

    /**
     * @param Job $job
     * @return string
     */
    protected function buildQueryUpdateJob(Job $job)
    {
        return sprintf('UPDATE abc_job SET status=%s, serialized_parameters=%s, serialized_response=%s, processing_time=%s, terminated_at=%s WHERE ticket=%s;',
            $this->getConnection()->quote((string)$job->getStatus()),
            $this->getConnection()->quote($job->getSerializedParameters()),
            $this->getConnection()->quote($job->getSerializedResponse()),
            (int)$job->getProcessingTime(),
            $this->convertDateToSql($job->getTerminatedAt()),
            $this->getConnection()->quote($job->getTicket())
        );
    }

    /**
     * @param Job $job
     * @return string
     */
    protected function buildQueryInsertJob(Job $job)
    {
        return sprintf('INSERT INTO abc_job (ticket, type, status, serialized_parameters, serialized_response, processing_time, created_at) VALUES(%s, %s, %s, %s, %s, %d, FROM_UNIXTIME(%d));',
            $this->getConnection()->quote($job->getTicket()),
            $this->getConnection()->quote($job->getType()),
            $this->getConnection()->quote((string)$job->getStatus()),
            $this->getConnection()->quote($job->getSerializedParameters()),
            $this->getConnection()->quote($job->getSerializedResponse()),
            (int)$job->getProcessingTime(),
            $this->createDateTime()->format('U')
        );
    }

    /**
     * @param array $pks
     * @return string
     */
    protected function buildQueryDeleteSchedulesByPks(array $pks)
    {
        return sprintf('DELETE FROM abc_job_schedule WHERE id IN (%s)', implode(',', $pks));
    }

    /**
     * @param string $jobTicket
     * @return string
     */
    protected function buildQueryDeleteSchedulesByJobTicket($jobTicket)
    {
        return sprintf('DELETE FROM abc_job_schedule WHERE job_ticket=%s;', $this->getConnection()->quote($jobTicket));
    }

    /**
     * @param string $jobTicket
     * @return string
     */
    protected function buildQuerySelectSchedulePKs($jobTicket)
    {
        return 'SELECT id FROM abc_job_schedule WHERE job_ticket=' . $this->getConnection()->quote($jobTicket);
    }

    /**
     * @param int               $pk
     * @param ScheduleInterface $schedule
     * @return string
     */
    protected function buildQueryUpdateSchedule($pk, ScheduleInterface $schedule)
    {
        $query = sprintf('UPDATE abc_job_schedule SET job_ticket=%s, type=%s, expression=%s, is_active=%s, updated_at=FROM_UNIXTIME(%d) WHERE id=%s;',
            $this->getConnection()->quote((string)$schedule->getJob()->getTicket()),
            $this->getConnection()->quote((string)$schedule->getType()),
            $this->getConnection()->quote((string)$schedule->getExpression()),
            (string)$schedule->getIsActive(),
            $this->createDateTime()->format('U'),
            $pk
        );

        return $query;
    }

    /**
     * @param ScheduleInterface $schedule
     * @return string
     */
    protected function buildQueryInsertSchedule(ScheduleInterface $schedule)
    {
        return sprintf('INSERT INTO abc_job_schedule (job_ticket, type, expression, is_active, updated_at, created_at) VALUES (%s, %s, %s, %s, %d, %d);',
            $this->getConnection()->quote((string)$schedule->getJob()->getTicket()),
            $this->getConnection()->quote((string)$schedule->getType()),
            $this->getConnection()->quote((string)$schedule->getExpression()),
            1,
            $this->createDateTime()->format('U'),
            $this->createDateTime()->format('U')
        );
    }

    /**
     * @param string        $time
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    private function createDateTime($time = 'now', $timezone = null)
    {
        return new \DateTime($time, $timezone);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection()
    {
        return $this->em->getConnection();
    }

    /**
     * @param JobInterface $job
     * @return bool|mixed|string
     */
    private function generateUuid(JobInterface $job)
    {
        return $this->uuidGenerator->generate($this->em, $job);
    }
}