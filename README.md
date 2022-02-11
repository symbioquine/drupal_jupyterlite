# JupyterLite

Adds a [JupyterLite](https://jupyterlite.readthedocs.io/en/latest/index.html) environment to Drupal.

*Note: Some branches and tags include only the built module. See the [development branch][development branch] for the full source code.*

## Installation

Use Composer and Drush to install jupyterlite in Drupal 9;

```sh
composer require drupal/jupyterlite
drush en jupyterlite
```

Then access JupyterLite at `/jupyterlite` under your Drupal installation. e.g. `https://my-drupal-site.example.com/jupyterlite`

*Available released versions can be viewed at https://www.drupal.org/project/jupyterlite*

## Development

### Building Locally

From the development branch;

```sh
docker build --tag build-jupyterlite-dist ./buildcontext
rm -rf jupyterlite-dist && mkdir jupyterlite-dist
docker run --rm -v $(pwd)/jupyterlite-dist:/jupyterlite-dist -e UID=$(id -u) -e GID=$(id -g) build-jupyterlite-dist
```

### Procedure for pushing new versions

From the [development branch][development branch] of this repository:

```sh
# Add/commit your changes
git add [...]
git commit
# Tag the release with the unbuilt prefix
git tag unbuilt-v9000.0.1
# Push the development branch and new tag
git push --atomic origin HEAD:development unbuilt-v9000.0.1
```

[development branch]: https://github.com/symbioquine/drupal_jupyterlite/tree/development
