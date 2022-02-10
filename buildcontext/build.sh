#!/bin/bash
set -e

poetry run jupyter lite init

cp -r /jupyterlite-temp-dist/* /jupyterlite-dist/
chown -R $UID:$GID /jupyterlite-dist
