Symfony Bundle
==============
The Symfony bundle allows using Data Pipeline in Symfony projects. It will take care of automatically wiring up all your nodes and registering them with the
pipeline.

Get started
-----------
Add the bundle to your `config/bundles.php`:
```php
<?php

return [
    // ...

    Matchory\DataPipe\Integration\Symfony\DataPipeBundle::class => [
        'all' => true
    ],
];
```

### Adding nodes
To add nodes, create them somewhere inside your `App/` namespace, or anywhere else configured in the services section of your configuration file. As long as
they implement the [`PipelineNodeInterface`](../../Interfaces/PipelineNodeInterface.php) (or any of its descendants), they will be discovered and added to the
pipeline registry automatically.

### Directory structure
We'd recommend the following directory structure:
```
<your-project>/
├── config/
└── src/
    ├── Collectors/
    │   ├── FirstCollector.php
    │   ├── SecondCollector.php
    │   └── NthCollector.php
    ├── Transformers/
    │   ├── FirstTransformer.php
    │   ├── SecondTransformer.php
    │   └── NthTransformer.php
    └── PipelineRunner.php
```

### Using the pipeline
Now, you may inject [`Pipeline`](../../Pipeline.php) as a dependency:
```php
use Matchory\DataPipe\Pipeline;

class Something {
    public function __construct(Pipeline $pipeline)
    {
        $pipeline->process(/* ... */);
    }
}
```
At this point, the pipeline will be fully configured and ready to use.

Node Discovery
--------------
Nodes are discovered during framework bootstrapping via [compiler passes](https://symfony.com/doc/current/service_container/compiler_passes.html). This means
any new nodes will only be discovered if you [compile your container](https://symfony.com/doc/current/components/dependency_injection/compilation.html), e.g. by
clearing your cache.

