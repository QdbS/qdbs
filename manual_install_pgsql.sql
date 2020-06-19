CREATE TABLE admins
(
  username character varying(16),
  password text,
  level integer DEFAULT 1,
  ip text,
  id serial NOT NULL,
  CONSTRAINT admins_pk PRIMARY KEY (id)
);

CREATE TABLE queue
(
  id serial NOT NULL,
  quote text,
  CONSTRAINT queue_pk PRIMARY KEY (id)
);

CREATE TABLE quotes
(
  id serial NOT NULL,
  quote text,
  rating integer DEFAULT 0,
  CONSTRAINT quotes_pk PRIMARY KEY (id)
);

CREATE TABLE settings
(
  template text,
  qlimit integer DEFAULT 0,
  heading character varying(80),
  title character varying(80),
  style text
);

CREATE TABLE votes
(
  id integer DEFAULT 0,
  ip text
);
