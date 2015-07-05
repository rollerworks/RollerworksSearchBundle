Basic usage
===========

FieldSets
---------

FieldSets can be generated per usage (see the RollerworksSearch documentation for details)
Or by registering then as services in the Service Container.

Registering FieldSets is done using the `rollerworks_search.fieldsets.fieldSetName` configuration.

``` yaml
# app/config/config.yml
rollerworks_search:
    fieldsets:
        users:
            fields:
                id:
                    type:           integer
                    required:       false
                    model_class:    "Acme\UserBundle\Model\User"
                    model_property: id
                    options:        []
                username:
                    type:           text
                    required:       false
                    model_class:    "Acme\UserBundle\Model\User"
                    model_property: name
```

Or by importing them from the model metadata.

``` yaml
# app/config/config.yml
rollerworks_search:
    fieldsets:
        users:
            import:
                -
                    class: "Acme\UserBundle\Model\User"
                    include_fields: [id, username]
```

Now the FieldSet can be referenced by its service-id `rollerworks_search.fieldset.users`.

    A FieldSet service is shared and not changeable.

```php
$fieldset = $container->get('rollerworks_search.fieldset.users');
```

Or by using the `rollerworks_search.fieldset_registry` service, which ensures
only FieldSets are returned.

```php
$fieldset = $container->get('rollerworks_search.fieldset_registry')->getFieldSet('users');
```

Input processors
----------------

An input-processor is created using the `rollerworks_search.input_factory` service.
Each input processor can be reused.

    Its also possible to create a input-processor by revering to
    `rollerworks_search.input.[processor-name]`, but this however does not guarantee
    the requested service is an input-processor, so be careful to validate the
    requested processor name!

```php
$filterQuery = $container->get('rollerworks_search.input_factory')->create('filter_query');
```

ConditionOptimizer
------------------

The 'main' condition optimizer is the `Rollerworks\Component\Search\ConditionOptimizer\ChainOptimizer`
which performs the registered optimizers in order.

Condition optimizer can be tagged with `rollerworks_search.condition_optimizer`
after which the search bundle will automatically register them.

The ChainOptimizer is available as the `rollerworks_search.chain_condition_optimizer` service.

```php
$formatter = $container->get(`rollerworks_search.chain_condition_optimizer`);
```

SearchProcessor
---------------

The SearchProcessor encapsulates a number of things for handling a search operation.
Helping you to reduce the complexity of a search system.

> The information is provided to get you going, the processor is much more advanced then shown here.
> In the future this whole section will be rewritten, for now if you get stuck please
> use ask for support on the [Gitter](https://gitter.im/rollerworks/RollerworksSearch) chanel. 

How does it work?

Simple, to ensure the search condition (or filtering preference) is applied for
every request to the page (controller) the SearchProcessor provides a so-called "SearchCode".
In practice the "SearchCode" is nothing more then an SearchCondition exported
to the FilterQuery format which is then compressed and encoded for usage in a URI.

Whenever you want use a different filter preference, the processor
will processes, optimize and export the condition for you!
 
> Don't worry about performance, the system comes provided with a cache mechanise.

If you followed the [Symfony Best Practices](http://symfony.com/doc/current/best_practices/index.html)
you should already have an AppBundle for keeping your code. 

The following examples reuse the concept of the AppBundle.

### Simple search with Doctrine ORM

This example uses the Symfony FrameworkBundle base controller, and a single search
operation for a list of users using Doctrine ORM.

*It's assumed you already have a simple User entity with no relational tables.*

```php
<?php

namespace AppBundle\Controller; 

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Rollerworks\Bundle\SearchBundle\Form\Type\SearchFormType;
use Rollerworks\Component\Search\Input\ProcessorConfig;

class UsersController extends Controller
{
    public function listAction(Request $request)
    {
        // You can get a FieldSet using the `rollerworks_search.fieldset_registry` service
        // when its configured in your application config.
        $fieldSet = $container->get('rollerworks_search.fieldset_registry')->getFieldSet('users');
    
        $config = new ProcessorConfig($fieldSet);
    
        $searchProcessor = $this->container->get('rollerworks_search.processor.search_processor_factory')->createProcessor($config, 'user');
        $searchProcessor->processRequest($request);
        
        // The SearchForm allows to provide a (new) search-condition. 
        // Optionally you can also configure the 'format', the default uses 'filter_query'.
        $form = $this->createForm('users', new SearchFormType(), ['filter' => $processor->exportSearchCondition('filter_query')]);
    
        // Check if this is a form post (applying a new search).
        if ($request->isMethod('POST') && $searchProcessor->isValid()) {
            // Redirect back the list with the filtering preference.
            // This will produce an uri like: user/?filter=eJxN3DuWBEkRBdGtsACEePWA3CiCDAmf3DodPMW6pR8AKmrDMr_Ub9-Z8
            return $this->redirect($this->generateUrl('homepage'), ['filter' => $processor->getSearchCode()]);
        }
        
        // Create the query
        $query = $this->createQuery('SELECT u FROM App:User u');
        
        // Apply the search-condition.
        // Always check as it can be invalid due to configuration changes. 
        if ($searchProcessor->isValid()) {
            $doctrineFactory = $this->container->get('rollerworks_search.doctrine_orm.factory');
            
            $whereBuilder = $factory->createWhereBuilder($query, $searchCondition);
            $whereBuilder->setEntityMapping('App:User', 'u');
    
            $whereBuilder = $factory->createCacheWhereBuilder($whereBuilder);
            $whereBuilder->setCacheKey($searchProcessor->getSearchCode());
            $whereBuilder->updateQuery(); // will update the query by append ' WHERE ' + the search condition
        }
        
        return $this->render(
            ':user:list.html.twig',
            [
                'data' => $query->getResult(),
                
                // Always pass any errors, even when the filtering preference is fetched from URI
                // it can be invalid due to configuration changes.
                'search_errors' => $searchProcessor->getErrors()
            ]
        );
    }
}
```

That's it! You just added a search function to your user list.

### Simple search with Doctrine ORM

Having a page with a single list and search is great, but what if you have multiple
lists on the same page and want to provide a search for each one of them?

Don't worry, the following example explains how to do this.

> Depending on how you build the form it may not be possible to post multiple condition at once.
> If you have multiple forms (`<form>`) its not possible to submit all of them in the same request.
> 
> Combining multiple conditions in one big form is also possible, but requires
> that all fields are within the same `<form>` HTML block.

This example uses the Symfony FrameworkBundle base controller, and two lists of data,
one for listing users and one for listing groups.

The main difference between the first example is that  

*It's assumed you already have a simple User entity with no relational tables.*

```php
<?php

namespace AppBundle\Controller; 

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Rollerworks\Bundle\SearchBundle\Form\Type\SearchFormType;
use Rollerworks\Component\Search\Input\ProcessorConfig;

class UsersController extends Controller
{
    public function listAction(Request $request)
    {
        // You can get a FieldSet using the `rollerworks_search.fieldset_registry` service
        // when its configured in your application config.
        $fieldSet = $container->get('rollerworks_search.fieldset_registry')->getFieldSet('users');
    
        $config = new ProcessorConfig($fieldSet);
    
        $searchProcessor = $this->container->get('rollerworks_search.processor.search_processor_factory')->createProcessor($config, 'user');
        $searchProcessor->processRequest($request);
        
        // The SearchForm allows to provide a (new) search-condition. 
        // Optionally you can also configure the 'format', the default uses 'filter_query'.
        $form = $this->createForm('users', new SearchFormType(), ['filter' => $processor->exportSearchCondition('filter_query')]);
    
        // Check if this is a form post (applying a new search).
        if ($request->isMethod('POST') && $searchProcessor->isValid()) {
            // Redirect back the list with the filtering preference.
            // This will produce an uri like: user/?filter=eJxN3DuWBEkRBdGtsACEePWA3CiCDAmf3DodPMW6pR8AKmrDMr_Ub9-Z8
            return $this->redirect($this->generateUrl('homepage'), ['filter' => $processor->getSearchCode()]);
        }
        
        // Create the query
        $query = $this->createQuery('SELECT u FROM App:User u');
        
        // Apply the search-condition.
        // Always check as it can be invalid due to configuration changes. 
        if ($searchProcessor->isValid()) {
            $doctrineFactory = $this->container->get('rollerworks_search.doctrine_orm.factory');
            
            $whereBuilder = $factory->createWhereBuilder($query, $searchCondition);
            $whereBuilder->setEntityMapping('App:User', 'u');
    
            $whereBuilder = $factory->createCacheWhereBuilder($whereBuilder);
            $whereBuilder->setCacheKey($searchProcessor->getSearchCode());
            $whereBuilder->updateQuery(); // will update the query by append ' WHERE ' + the search condition
        }
        
        return $this->render(
            ':user:list.html.twig',
            [
                'data' => $query->getResult(),
                
                // Always pass any errors, even when the filtering preference is fetched from URI
                // it can be invalid due to configuration changes.
                'search_errors' => $searchProcessor->getErrors()
            ]
        );
    }
}
```
