FROM mongo:6.0.3
COPY products.json /products.json
COPY import.sh /import.sh
ENTRYPOINT ./import.sh
