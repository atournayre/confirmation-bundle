# Confirmation Bundle

This bundle provide confirmation for entities.

## Install
### Composer
```shell
composer require atournayre/confirmation-bundle
```
### Bundles
```php
// config/bundles.php

return [
    // ...
    Atournayre\Bundle\ConfirmationBundle\AtournayreConfirmationBundle::class => ['all' => true],
    // ...
];
```

### Configuration
Create file and copy content below.
```yaml
# config/packages/atournayre_confirmation.yaml
atournayre_confirmation:
  providers:
    # Example for an email provider
    # email: App\Provider\YourEmailProvider
    # Others providers can be anything : sms, pigeon...
```

### Routing
Create file and copy content below.
```yaml
# config/routes/atournayre_confirmation.yaml
atournayre_confirmation:
  resource: "@AtournayreConfirmationBundle/Resources/config/routing.yaml"
```

### Services
```yaml
# config/services.yaml
services:
  _instanceof:
    Atournayre\Bundle\ConfirmationBundle\Provider\AbstractProvider:
      tags: ['atournayre.confirmation_bundle.tag.provider']

  Atournayre\Bundle\ConfirmationBundle\Controller\:
    resource: ../vendor/atournayre/confirmation-bundle/src/Controller
    public: true
    tags: ['controller.service_arguments']

  Symfony\Component\DependencyInjection\ContainerInterface: '@service_container'

  Atournayre\Bundle\ConfirmationBundle\Service\ConfirmationCodeService:
    class: Atournayre\Bundle\ConfirmationBundle\Service\ConfirmationCodeService
    arguments:
      $container: '@service_container'

  Atournayre\Bundle\ConfirmationBundle\Repository\ConfirmationCodeRepository:
    class: Atournayre\Bundle\ConfirmationBundle\Repository\ConfirmationCodeRepository

  Atournayre\Bundle\ConfirmationBundle\Config\LoaderConfig:
    class: Atournayre\Bundle\ConfirmationBundle\Config\LoaderConfig

  Atournayre\Bundle\ConfirmationBundle\Service\GenerateConfirmationService:
    class: Atournayre\Bundle\ConfirmationBundle\Service\GenerateConfirmationService

  # Providers needs to be public
  App\Provider\YourCustomProvider:
    class: App\Provider\YourCustomProvider
    public: true
```

## Usage
### Configure entity
1. Entity needs to implement `Atournayre\Bundle\ConfirmationBundle\Contracts\ConfirmableInterface`.
2. Add `Atournayre\Bundle\ConfirmationBundle\Traits\ConfirmableTrait` to your entity.

### Create a provider
For each entity/mapping, you must :
1. Create a provider
2. It must extend from `Atournayre\Bundle\ConfirmationBundle\Provider\AbstractProvider`
3. You need to implement abstract methods
4. You can override public methods

> NOTE
> 
> It's discouraged to override `updateEntity()`. 
> 
> Prefer using `updateAfterConfirmation()` inside the entity to update it after confirmation (example: update status from 'pending' to 'valid').


### Generate confirmation code
When you want to generate a confirmation code, just use `Atournayre\Bundle\ConfirmationBundle\Service\GenerateConfirmationService`, call the `__invoke()` method.

Following actions will be performed :
1. A confirmation code will be generated
2. Entity will be tagged as "unconfirmed"
3. The recipient will be notified.

Tip : If you use both id and uuid in your entity, you can specify the id to use as 3rd parameter.

As code are required for verification purpose, the service only send it and don't return it.

### Verify confirmation code
Verification can be performed using 2 ways :
* Direct link
* Form

#### Direct link
Using `app_confirmation_code_with_code` route, the user only needs to follow the link and entity will be validated.

#### Form
Using `app_confirmation_code` route, the user needs to fill-in a form with the code provided to him (via notification) so the entity could be validated.

## Templating
It is possible to override any template thanks to Symfony.

## Contributing
Of course, open source is fueled by everyone's ability to give just a little bit
of their time for the greater good. If you'd like to see a feature or add some of
your *own* happy words, awesome! Tou can request it - but creating a pull request
is an even better way to get things done.

Either way, please feel comfortable submitting issues or pull requests: all contributions
and questions are warmly appreciated :).
