#!/bin/bash
set -e

poetry run jupyter lite init

poetry run jupyter lite build --output-dir /jupyterlite-temp-dist

cp -r /jupyterlite-temp-dist/* /jupyterlite-dist/
chown -R $UID:$GID /jupyterlite-dist
