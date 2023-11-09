#!/bin/sh

set -x

mysql -uroot -proot -h127.0.0.1 < create_tables.sql

#wget -O data/allCountries.zip https://download.geonames.org/export/dump/allCountries.zip
#unzip -o data/allCountries.zip -d data
#rm data/allCountries.zip

wget -O data/FR.zip https://download.geonames.org/export/dump/FR.zip
unzip -o data/FR.zip -d data
rm data/FR.zip

wget -O data/hierarchy.zip https://download.geonames.org/export/dump/hierarchy.zip
unzip -o data/hierarchy.zip -d data
rm data/hierarchy.zip

wget -O data/alternateNames.zip https://download.geonames.org/export/dump/alternateNames.zip
unzip -o data/alternateNames.zip -d data
rm data/alternateNames.zip

wget -O data/admin1CodesASCII.txt https://download.geonames.org/export/dump/admin1CodesASCII.txt

wget -O data/featureCodes_en.txt https://download.geonames.org/export/dump/featureCodes_en.txt

wget -O data/timeZones.txt https://download.geonames.org/export/dump/timeZones.txt

wget -O data/countryInfo.txt https://download.geonames.org/export/dump/countryInfo.txt
sed -i '/^#/d' data/countryInfo.txt

cat > data/continentCodes.txt << EOL
AF,Africa,6255146
AS,Asia,6255147
EU,Europe,6255148
NA,North America,6255149
OC,Oceania,6255151
SA,South America,6255150
AN,Antarctica,6255152
EOL

chmod 666 data/*.txt

#time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
#  SET NAMES utf8mb4;
#  LOAD DATA LOCAL INFILE 'data/allCountries.txt'
#  INTO TABLE geonames CHARACTER SET utf8mb4 (id,name,ascii_name,alternate_names,latitude,longitude,fclass,fcode,country,cc2, admin1,admin2,admin3,admin4,population,@elevation,gtopo30,time_zone,updated_at)
#  SET elevation=IF(@elevation='',NULL,@elevation);
#  SHOW WARNINGS;
#"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/FR.txt'
  INTO TABLE geonames CHARACTER SET utf8mb4 (id,name,ascii_name,alternate_names,latitude,longitude,fclass,fcode,country,cc2, admin1,admin2,admin3,admin4,population,@elevation,gtopo30,time_zone,updated_at)
  SET elevation=IF(@elevation='',NULL,@elevation);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/hierarchy.txt'
  INTO TABLE hierarchy CHARACTER SET utf8mb4 (parent_id,child_id,type);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/alternateNames.txt'
  INTO TABLE alternate_names CHARACTER SET utf8mb4 (id,geoname_id,language_code,alternate_name,@is_preferred_name,@is_short_name,@is_colloquial,@is_historic)
  SET
    is_preferred_name=IF(@is_preferred_name='',FALSE,@is_preferred_name),
    is_short_name=IF(@is_short_name='',FALSE,@is_short_name),
    is_colloquial=IF(@is_colloquial='',FALSE,@is_colloquial),
    is_historic=IF(@is_historic='',FALSE,@is_historic)
  ;
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/continentCodes.txt'
  INTO TABLE continents CHARACTER SET utf8mb4 FIELDS TERMINATED BY ',' (code, name, geoname_id);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/countryInfo.txt'
  INTO TABLE countries CHARACTER SET utf8mb4 IGNORE 1 LINES (iso2,iso3,iso_numeric,fips_code,name,capital,area_in_sq_km,population,continent_code,tld,currency_code,currency_name,phone,postal_code_format,postal_code_regex,languages,geoname_id,neighbours,equivalent_fips_code);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/iso-languagecodes.txt'
  INTO TABLE language_codes CHARACTER SET utf8mb4 IGNORE 1 LINES (iso_639_3, iso_639_2, iso_639_1, language_name);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/admin1CodesAscii.txt'
  INTO TABLE admin1_codes CHARACTER SET utf8mb4 (code, name, name_ascii, geoname_id);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/featureCodes_en.txt'
  INTO TABLE feature_codes CHARACTER SET utf8mb4 (code, name, description);
  SHOW WARNINGS;
"
time mysql -v --local-infile=1 -uroot -proot -h127.0.0.1 geonames -e "
  SET NAMES utf8mb4;
  LOAD DATA LOCAL INFILE 'data/timeZones.txt'
  INTO TABLE time_zones CHARACTER SET utf8mb4 IGNORE 1 LINES (country_code, id, gmt_offset, dst_offset, raw_offset);
  SHOW WARNINGS;
"

# rm -f data/*.txt
