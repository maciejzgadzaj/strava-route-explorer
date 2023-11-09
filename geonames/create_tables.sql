CREATE DATABASE IF NOT EXISTS geonames;
USE geonames;

DROP TABLE IF EXISTS time_zones;
DROP TABLE IF EXISTS hierarchy;
DROP TABLE IF EXISTS alternate_names;
DROP TABLE IF EXISTS countries;
DROP TABLE IF EXISTS continents;
DROP TABLE IF EXISTS language_codes;
DROP TABLE IF EXISTS admin1_codes;
DROP TABLE IF EXISTS feature_codes;
DROP TABLE IF EXISTS geonames;

CREATE TABLE geonames (
	id int,
	name varchar(200),
	ascii_name varchar(200),
	alternate_names text,
	latitude decimal(10,7),
	longitude decimal(10,7),
	fclass char(1),
	fcode varchar(10),
	country varchar(2),
	cc2 varchar(60),
	admin1 varchar(20),
	admin2 varchar(80),
	admin3 varchar(20),
	admin4 varchar(20),
	population int,
	elevation int,
	gtopo30 int,
	time_zone varchar(40),
	updated_at date
) CHARACTER SET utf8mb4;

CREATE TABLE hierarchy (
    parent_id int,
    child_id int,
    type varchar(50)
) CHARACTER SET utf8mb4;

CREATE TABLE alternate_names (
	id int,
	geoname_id int,
	language_code varchar(7),
	alternate_name varchar(200),
	is_preferred_name boolean,
	is_short_name boolean,
	is_colloquial boolean,
	is_historic boolean
) CHARACTER SET utf8mb4;

CREATE TABLE continents (
    code CHAR(2),
    name VARCHAR(20),
    geoname_id INT
) CHARACTER SET utf8mb4;

CREATE TABLE countries (
	iso2 char(2),
	iso3 char(3),
	iso_numeric integer,
	fips_code varchar(3),
	name varchar(200),
	capital varchar(200),
	area_in_sq_km double precision,
	population integer,
	continent_code char(2),
	tld char(3),
	currency_code char(3),
	currency_name char(20),
	phone varchar(20),
	postal_code_format varchar(60),
	postal_code_regex varchar(200),
	languages varchar(200),
	geoname_id int,
	neighbours varchar(50),
	equivalent_fips_code char(10)
) CHARACTER SET utf8mb4;

CREATE TABLE language_codes(
	iso_639_3 char(4),
	iso_639_2 varchar(50),
	iso_639_1 varchar(50),
	language_name varchar(200)
) CHARACTER SET utf8mb4;

CREATE TABLE admin1_codes (
	code char(11),
	name text,
	name_ascii text,
	geoname_id int
) CHARACTER SET utf8mb4;

CREATE TABLE feature_codes (
	code char(7),
	name varchar(200),
	description text
) CHARACTER SET utf8mb4;

CREATE TABLE time_zones (
	id VARCHAR(200),
    country_code CHAR(2),
	gmt_offset DECIMAL(4,2),
	dst_offset DECIMAL(4,2),
	raw_offset DECIMAL(4,2)
) CHARACTER SET utf8mb4;
