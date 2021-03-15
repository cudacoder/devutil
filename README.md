# Cloud Utils

This project is a failed attempt at wrapping all of my most frequently accessed tools and commands inside a *single* binary, written in PHP.

It was meant to be used in order to setup a development environment using Docker, and to manage cloud resources on AWS using the AWS SDK.

Since there are better solutions for this type of issue (just take a look at Terraform for starters), I decided to drop this project and leave it only as a digital memory in an endless sea of repositories...

Needless to say, this was a _terrible_ idea (not to mention the implementation which is horrible), but I had a blast developing this utility.

The development process taught me quite a lot about the tools I'm using on a regular basis.
I hope my poor attempt will be of service to anyone reading this, thank you for stopping by!

## Build
I've included a `build.php` script to package this project as a `PHAR` if someone wants to do that for whatever reason.

Simply run `php build.php` to generate the `devutil.phar` executable.
