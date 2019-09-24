DROP VIEW IF EXISTS users_and_teams_view;
CREATE OR REPLACE VIEW users_and_teams_view AS
select id,entity_id,name,type
from (
            select row_number() over (order by entity_id) as id,
                   entity_id,
                   name,
                   type
            from (
                        SELECT id                                    as entity_id,
                               concat_ws(' ', first_name, last_name) as name,
                               text 'user'                           as type
                        FROM users
                        UNION
                        SELECT id          as entity_id,
                               name,
                               text 'team' as type
                        FROM teams
                 ) AS union_query
     ) as sub_query;
