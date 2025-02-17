FROM python:3.9-bookworm AS release
RUN apt-get update && apt-get install -y build-essential libffi-dev libssl-dev git && pip install poetry
COPY ./pyproject.toml /build/
COPY ./poetry.lock /build/
WORKDIR /build
RUN poetry install --only main --no-root
COPY ./build.sh /build/
RUN chmod u+x /build/build.sh
ENV JUPYTERLITE_OUTPUT_DIR=/jupyterlite-temp-dist/
CMD ["/build/build.sh"]
