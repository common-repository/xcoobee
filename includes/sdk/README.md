# XcooBee PHP SDK

The XcooBee SDK is a facility to abstract lower level calls and implement standard behaviors.
The XcooBee team is providing this to improve the speed of implementation and show the best practices while interacting with XcooBee.

Generally, all communication with XcooBee is encrypted over the wire since none of the XcooBee systems will accept plain traffic. All data sent to XcooBee from you and vice versa is going to use encryption. In addition, non-bee event communication to you is also signed using your PGP key.

If you need to generate new PGP keys you can login to your XcooBee account and go to the `Settings` page to do so.

XcooBee systems operate globally but with regional connections. The SDK will be connecting you to your regional endpoint automatically.

There is detailed and extensive [API documentation](API.md).

# Installation

`composer require xcoobee/xcoobee-sdk`  

# Usage

Before using the SDK, it needs to be configured.
Once created, the configuration can be set on the SDK instance. In this case, each function call will fallback to using this configuration.
Alternatively, you may pass a reference to a configuration instance to each function call. This passed configuration will take precedence over any configuration set on the SDK instance.

```php
use XcooBee\XcooBee;

$sdk = new XcooBee();
```

## The config object

The config object carries all basic configuration information for your specific setup and use. It can be transparent handled by the SDK or specifically passed into every function.
The basic information in the configuration is:

- your api-key from XcooBee
- your api-secret from XcooBee
- your pgp-secret/passphrase either from you or XcooBee
- your default campaign Id from XcooBee


The SDK will attempt to determine the configuration object based on the following schema:

1.) Use the configuration object passed into the function.

2.) Use the information as set by the setConfig call.

3.) Check the file system for the config file (.xcoobee/config)

```php
$config = \XcooBee\Models\ConfigModel::createFromFile();
```
or
```php
$config = \XcooBee\Models\ConfigModel::createFromData([
    'apiKey'    => '',
    'apiSecret' => '',
]);
```

```
apiKey      => the api-key as supplied by XcooBee account
apiSecret   => the api-secret (downloaded when api key was created at XcooBee)
pgpSecret   => the pgp-secret key either generated by XcooBee or supplied by you
pgpPassword => the pgp-password supplied by you
campaignId  => the default campaign Id from your campaign on XcooBee
pageSize    => pagination limit (records per page), default based on recordset, max possible is 100
```

### setConfig(config)

The `setConfig` call is the mechanism to create the initial configuration object. You can use it multiple times. Each time you call you will override the existing data. The data once set will persist until library is discarded or `clearConfig` is called.

```php
$sdk->setConfig($config);
```

### clearConfig()

Removes all configuration data in the configuration object.

### config on file system

XcooBee SDK will search the file system for configuration info as last mechanism. You should ensure that the access to the config files is not public and properly secured. Once found the information is cached and no further lookup is made. If you change your configuration you will need to restart the process that is using the SDK to pick up the changes.

We recommend that you also look into how to encrypt the contents of these files with OS specific encryption for use only by the process that uses the XcooBee SDK.

The files will be located inside your `home` directory in the `.xcoobee` subdirectory. Thus the full path to config are:

`/[home]/.xcoobee/config` => the configuration options

`/[home]/.xcoobee/pgp.secret` => the pgp secret key in separate file


on Windows it is in the root of your user directory

`/Users/MyUserDir/.xcoobee/config` => the configuration option

`/Users/MyUserDir/.xcoobee/pgp.secret` => the pgp secret key in separate file

The initial content of the config file is plain text, with each option on a separate line.

**example file**:
```
apiKey=8sihfsd89f7
apiSecret=8937438hf
campaignId=ifddb4cd9-d6ea-4005-9c7a-aeb104bc30be
pgpPassword=somethingsecret
pageSize=10
```

options:

```
apiKey      => the api-key
apiSecret   => the api-secret
campaignId  => the default campaign id
pgpPassword => the password for your pgp key
pageSize    => pagination limit
```

## Example

```php
$bees = $sdk->bees->listBees()->result;
```

More examples see in `example` folder.


# Tests

## Running Unit Tests

You can use the following command line to run unit test to validate the project

`phpunit -c ./test/phpunit.xml`

## Running Integration Tests

When your initial developer account is created it will be populated with data so that you can test the project against actual data and system.
You will have to configure your `test/integration/assets/config/.xcoobee/config` file prior to running the integration tests.

You can use a command line to run the integration tests for this project. You will need to **clone the repo** from GitHub and run the following command line:

`phpunit -c ./test/integration/phpunit.xml`

