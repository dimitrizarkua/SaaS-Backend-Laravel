DROP VIEW IF EXISTS users_and_teams_view;
CREATE OR REPLACE VIEW users_and_teams_view AS
SELECT id, entity_id, name, type, updated_at
FROM (
         SELECT row_number() OVER (ORDER BY entity_id) AS id,
                entity_id,
                name,
                type,
                updated_at
         FROM (
                  SELECT id                                    AS entity_id,
                         concat_ws(' ', first_name, last_name) AS name,
                         text 'user'                           AS type,
                         updated_at
                  FROM users
                  UNION
                  SELECT id          AS entity_id,
                         name,
                         text 'team' AS type,
                         updated_at
                  FROM teams
              ) AS union_query
     ) AS sub_query;
