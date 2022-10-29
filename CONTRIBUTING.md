# Contribution Guide

## Bug Reports

To encourage active collaboration, Saeghe strongly encourages pull requests, not just bug reports. 
Pull requests will only be reviewed when marked as "ready for review" (not in the "draft" state) and all tests for new features are passing. 
Lingering, non-active pull requests left in the "draft" state will be closed after a few days.

However, if you file a bug report, your issue should contain a title and a clear description of the issue. 
You should also include as much relevant information as possible and a code sample that demonstrates the issue. 
The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

Remember, bug reports are created in the hope that others with the same problem will be able to collaborate with you on solving it. 
Do not expect that the bug report will automatically see any activity or that others will jump to fix it. 
Creating a bug report serves to help yourself and others start on the path of fixing the problem. 
If you want to chip in, you can help out by fixing [any bugs listed in our issue trackers](https://github.com/saeghe/saeghe/labels/bug). 
You must be authenticated with GitHub to view all of Saeghe's issues.

## Support Questions

Saeghe's GitHub issue trackers are not intended to provide Saeghe help or support. Instead, use the [GitHub Discussions](https://github.com/saeghe/saeghe/discussions) channel.

## Core Development Discussion

You may propose new features or improvements of existing Saeghe behavior in the Saeghe repository's [GitHub discussion board](https://github.com/saeghe/saeghe/discussions). 
If you propose a new feature, please be willing to implement at least some of the code that would be needed to complete the feature.

## Security Vulnerabilities

If you discover a security vulnerability within Saeghe, please send an email to [Morteza Poussaneh](mailto:morteza@protonmail.com?subject=[GitHub]%20Security%20Vulnerabilities%20Report). 
All security vulnerabilities will be promptly addressed.

## Code of Conduct

In order to ensure that the Saeghe community is welcoming to all, please review and abide by the [Code of Conduct](https://github.com/saeghe/saeghe/blob/master/CODE_OF_CONDUCT.md).

## Installation for Contributors

In order to install Saeghe for contributing, follow these steps:

```shell
git@github.com:saeghe/saeghe.git
cd saeghe
git submodule init
git submodule update
```

## Running tests

In order to install Saeghe for contributing, run the following command:

```shell
./test-runner
```
