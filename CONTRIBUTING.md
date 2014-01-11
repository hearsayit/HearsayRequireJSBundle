# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github][1].

## Pull Requests

- **[PSR-2 Coding Standard][2]** - The easiest way to apply the conventions is
to install [PHP Code Sniffer][3].

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the README and any other
relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [semver][4]. Randomly
breaking public APIs is not an option.

- **Create topic branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send
multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull
request is meaningful. If you had to make multiple intermediate commits while
developing, please squash them before submitting.

## Running Tests

```sh
$ wget https://raw.github.com/jrburke/r.js/2.1.8/dist/r.js
$ phpunit --coverage-text
```

**Happy coding**!

[1]: https://github.com/hearsayit/HearsayRequireJSBundle
[2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[3]: https://github.com/squizlabs/PHP_CodeSniffer
[4]: http://semver.org
