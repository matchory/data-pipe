Data Pipe [![Latest Stable Version](http://poser.pugx.org/matchory/data-pipe/v)](https://packagist.org/packages/matchory/data-pipe) [![Total Downloads](http://poser.pugx.org/matchory/data-pipe/downloads)](https://packagist.org/packages/matchory/data-pipe) [![Latest Unstable Version](http://poser.pugx.org/matchory/data-pipe/v/unstable)](https://packagist.org/packages/matchory/data-pipe) [![License](http://poser.pugx.org/matchory/data-pipe/license)](https://packagist.org/packages/matchory/data-pipe)
=========
> An opinionated framework for building data enrichment pipelines in PHP

Data Pipe is a framework to create data enrichment pipelines in PHP. Such an application works by taking a piece of information, enriching it with additional
data, and enhancing that data by applying transformations on them.

As a more tangible example, take a _customer_ pipeline: It ingests the name of a customer, retrieves their _shopping history_ and _age_, then enhances the
record by removing old items from the shopping history, and assigning a targeting group to the customer.

While that, of course, merely describes some arbitrary business logic, Data Pipe helps you to describe this process with a set of reusable, composable, and
encapsulated steps!

Installation
------------
Install the library as a dependency using composer:
```bash
php composer require matchory/data-pipe
```

Usage
-----
> **Note:** Before getting started with Data Pipe, you should familiarize
> yourself with [its core concepts](#core-concepts).

Data Pipe works by setting up pipelines with a pre-configured set of inter-dependent nodes. There are currently two types of nodes:
[Data enriching nodes](#data-enriching-nodes) and
[post-processing-nodes](#post-processing-nodes) (which are both variants of generic nodes).  
Nodes take a payload object, modify and return it. Enriching nodes add new data, post-processing nodes transform existing values. This distinction might seem
irrelevant, but it allows lots of runtime-optimizations.

### Creating nodes
In its simplest form, an enriching node might look like this:
```php
use Matchory\DataPipe\Nodes\AbstractCollector as Node;
use Matchory\DataPipe\PipelineContext;

class MyNode extends Node
{
    public function __construct(protected $yourInternalAgeApi) {}

    public function pipe(PipelineContext $context): PipelineContext
    {
        // Work with the data payload
        $email = $context->getPayload()->getAttribute('email');
        
        // Perform domain-specific work
        $age = $this->yourInternalAgeApi->query($email);
        
        // Update the payload
        if ($age) {
            $context->proposeChange($this, 'age', $age);
        }
        
        return $context;
    }
}
```

### Proposing changes
Note that you cannot directly update the payload: Every node receives just a clone of the actual payload. Instead, you can _propose_ a change to the payload.
Data Pipe provides a simple algorithm for
[best-fit change application](#best-fit-change-application). This allows to keep and compare multiple values for a single field.

### Creating pipelines
Now that we have a node, let's create a pipeline to add it to:
```php
use Matchory\DataPipe\Payload\Payload;
use Matchory\DataPipe\Pipeline;
use Symfony\Component\EventDispatcher\EventDispatcher;

$nodes = [
    new MyNode(),
];
$eventDispatcher = new EventDispatcher();
$pipeline = new Pipeline($nodes, $eventDispatcher);

function(): Generator {
    yield new Payload([
        'email' => 'foo@bar.com'
    ]);
}

$pipeline->process(fetchNextPayload());
```

### DI usage
This is a contrived example, of course; in reality, a dependency-injection container would handle almost everything for you:
```php
use Matchory\DataPipe\Pipeline;

class EntryPoint {
    public function main(Pipeline $pipeline, Generator $recordFetcher): void
    {
        foreach ($recordFetcher as $record) {
            $pipeline->process($recordFetcher);
        }
    }
}
```

Core Concepts
-------------
Data Pipe uses a few building blocks to structure your pipelines.

### Pipeline nodes
Nodes are the stages forming a pipeline. They can depend on other nodes to have been executed previously; these dependencies will be figured out before the
pipeline runs, so you don't have to define an order manually. Every payload processed by the pipeline will be piped to all nodes in it, each having the option
to suggest changes to the data.  
There are two types of nodes currently:

#### Data enriching nodes
Nodes that enhance a record with additional information are _data enriching nodes_. These nodes may optionally define a _cost_: It is used to order those nodes
by cost, and determine whether executing additional nodes is even necessary.  
Imagine you have two data sources -- your own, internal database, and an external system that charges per API call. The node for your database will have a lower
cost than that or the external API. Now, if we're looking for a piece of information, we'll first execute the "cheaper" node (your internal database), then,
_only if it can't satisfy our request_, we'll also execute the more expensive node.

The more nodes you have, the more apparent the advantage of granular costs will be: Information will always be acquired with the cheapest means possible.

#### Post-processing nodes
Post-processing nodes allow you to refine, modify, or compare previously gathered information. This is different from data enriching nodes, as they're typically
executed _after_ those nodes.

### Best-Fit change application
The more data sources you have, the more variants of pieces of information you will collect. What's problematic is determining the _best_ of those variants -
think of an email address for example:

- dxdtnfa1n5@privaterelay.appleid.com
- foobar@trashmail.to
- john.doe@company.com
- john.doe+yourdomain.tld@gmail.com
- john.doe@gmail.com

Depending on a few rules, you're probably able to infer which is the closest variant to what you're looking for. Now, to keep a sequence of nodes from
overriding each other's results, instead of setting an attribute on the payload, they can _suggest changes_ instead:

```php
$context->proposeChange($this, 'field_name', 42);
```

All nodes may propose changes to existing data, along with an optional _confidence score_: In the email case above, for example, we'd probably have a grey-list
of trashmail domains, and assign that address a low confidence score. The idea here is, _take that email if nothing better can be found later on_.
