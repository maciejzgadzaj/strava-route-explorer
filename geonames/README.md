# GeoNames

The GeoNames geographical database covers all countries and contains over eleven million placenames that are available for download free of charge.

- [Home page](https://www.geonames.org/)
- [Free Gazetteer Data](https://download.geonames.org/export/dump/)
- [Importing *all* geonames tables to mysql](http://forum.geonames.org/gforum/posts/list/732.page)

## Local install

- modify and run `import.sh` (modify first if needed, with db connection details, or to import full `data/allCountries.txt` instead of default small `data/FR.txt`)
- modify and import `create_indexes.sql`
