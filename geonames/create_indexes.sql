
ALTER TABLE geonames ADD PRIMARY KEY (id);
CREATE INDEX idx_latitude ON geonames (latitude);
CREATE INDEX idx_longitude ON geonames (longitude);
CREATE INDEX idx_fclass ON geonames (fclass);
CREATE INDEX idx_fcode ON geonames (fcode);

CREATE INDEX idx_parent_id ON hierarchy (parent_id);
CREATE INDEX idx_child_id ON hierarchy (child_id);
ALTER TABLE hierarchy ADD CONSTRAINT fk_hierarchy_parent_id FOREIGN KEY (parent_id) REFERENCES geonames(id);
ALTER TABLE hierarchy ADD CONSTRAINT fk_hierarchy_child_id FOREIGN KEY (child_id) REFERENCES geonames(id);

ALTER TABLE alternate_names ADD PRIMARY KEY (id);
ALTER TABLE alternate_names ADD CONSTRAINT fk_alternate_names_geonames_id FOREIGN KEY (geoname_id) REFERENCES geonames(id);

ALTER TABLE continents ADD PRIMARY KEY (id);
ALTER TABLE continents ADD CONSTRAINT fk_continents_geonames_id FOREIGN KEY (geoname_id) REFERENCES geonames(id);

ALTER TABLE countries ADD PRIMARY KEY (iso2);
ALTER TABLE countries ADD CONSTRAINT fk_country_info_geonames_id FOREIGN KEY (geoname_id) REFERENCES geonames(id);
ALTER TABLE countries ADD CONSTRAINT fk_countries_continent FOREIGN KEY (continent_code) REFERENCES continents(code);

ALTER TABLE admin1_codes ADD PRIMARY KEY (code);
ALTER TABLE admin1_codes ADD CONSTRAINT fk_admin1_codes_geoname_id FOREIGN KEY (geoname_id) REFERENCES geonames(id);

ALTER TABLE feature_codes ADD PRIMARY KEY (code);

ALTER TABLE time_zones ADD PRIMARY KEY (id);
ALTER TABLE time_zones ADD CONSTRAINT fk_time_zones_countries FOREIGN KEY (country_code) REFERENCES countries(iso2);
