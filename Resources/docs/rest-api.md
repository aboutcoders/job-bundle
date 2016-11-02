REST-API
========

The AbcJobBundle ships with a JSON REST-API. To use this you need to make sure the following bundles are installed and configured:
 
* [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle)

Next you need to make sure that the routing files are imported:

```yaml
# app/config/routing.yml
abc-rest-job:
    type: rest
    resource: "@AbcJobBundle/Resources/config/routing/rest-all.xml"
    prefix: /api
```

You can now see an overview of all available API methods using API documentation provided by the [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle).