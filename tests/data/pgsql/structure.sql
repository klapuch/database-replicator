CREATE TABLE users (
  id serial NOT NULL,
  name text NOT NULL,
  surname text NOT NULL
);

ALTER TABLE users ADD CONSTRAINT users_id PRIMARY KEY (id);
