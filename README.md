Symfony stunnel bundle
======================

[![Symfony 3.x][1]][2]

The stunnel bundle allows you to implement HTTPS connectivity in your local development environment through the use
of [stunnel](https://www.stunnel.org).

Installation
------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require --dev torfs-ict/stunnel-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new TorfsICT\StunnelBundle\TorfsICTStunnelBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Copy your certificate

Copy your certicate chain and private key into the project e.g. `app/config/letsencrypt/fullchain.pem` and
`app/config/letsencrypt/privkey.pem`.

### Step 4: Configuration

#### Sample configuration

```yaml
# app/config/config_dev.yml
torfs_ict_stunnel:
  accept_host: dev.domain.com
  forward_host: 192.168.1.15
  forward_port: 80
  fullchain: "%kernel.project_dir%/app/config/letsencrypt/fullchain.pem"
  privkey: "%kernel.project_dir%/app/config/letsencrypt/privkey.pem"
```

#### Configuration options

- `accept_host`: The domain for which your certificate is made.
- `forward_host`: The IP address or hostname of your webserver.
- `forward_port`: The port used by your webserver (defaults to `80`).
- `fullchain`: The filesystem location of your certificate chain.
- `privkey`: The filesystem location of your certificate private key.
- `nanobox`: Boolean indicating if you want to enable [Nanobox integration](#nanobox-integration) (defaults to `false`).

Nanobox integration
-------------------

When using [Nanobox](https://nanobox.io) you can omit the `accept_host` and `forward_host` as these will be retrieved
from the Nanobox configuration. Do note that only a single host can be used, so if you have multiple DNS aliases 
defined only the last one will be used.

Usage
-----

### Hosts setup

Using stunnel requires `dev.domain.org` to point to `127.0.0.1`, so make sure to add it to your systems `hosts` file if
required.

### Starting stunnel

Simply use the Symfony console component and execute the command to start stunnel. For Windows the stunnel binaries
are included, in Linux you should make sure that stunnel is installed (for Ubuntu `sudo apt-get install stunnel4`).

```console
php bin/console stunnel
``` 

In Windows an icon in the system tray will be visible, in Linux the stunnel process will be started in the background.

Once stunnel is running you can navigate to [https://dev.domain.com](https://dev.domain.com) and notice that your site
is now secured with your certificate.

License
-------

This software is published under the [MIT License](LICENSE.md).

 [1]: https://img.shields.io/badge/symfony-3.x-green.svg 
 [2]: https://symfony.com/