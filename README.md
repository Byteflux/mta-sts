# mta-sts.php

A simple PHP script for generating a MTA-STS policy file.

The main purpose of this project is to build and deploy a policy file on [Cloudflare Pages](https://pages.cloudflare.com/).

## Configuration

The policy file is configured using environment variables.

Environment Variable | Description | Example
-------------------- | ----------- | -------
`POLICY_MODE` | The policy enforcement mode. Valid values are: `testing`, `enforce` or `none` | `testing`
`POLICY_MX` | List of MX hostnames separated by `,`, `:`, `;` or line feed | `mx1.example.com,mx2.example.com`
`POLICY_MAX_AGE` | The duration in seconds that the policy should be cached by senders. Should be `>= 86400` and `<= 31557600` | `86400`

## Generating the policy file

Once the environment variables have been configured, the policy file is ready to be generated.

Run the `mta-sts.php` script to generate a new policy file:

```console
$ export POLICY_MODE=testing
$ export POLICY_MX=mx1.example.com,mx2.example.com
$ export POLICY_MAX_AGE=86400
$ php mta-sts.php
Writing policy file...

    version: STSv1
    mode: testing
    mx: mx1.example.com
    mx: mx2.example.com
    max_age: 86400

Policy file generated at dist/.well-known/mta-sts.txt

Remember to update the policy ID in your '_mta-sts' TXT records.
Example record using the current date and time:

    v=STSv1; id=20211018010409
```
