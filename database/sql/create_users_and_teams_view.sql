CREATE OR REPLACE VIEW users_and_teams_view AS
select id,name,type
from (
       SELECT
         id,
         concat_ws(' ', first_name, last_name) as name,
         text 'user'                           as type
       FROM users
       UNION
       SELECT
         id,
         name,
         text 'team' as type
       FROM teams
     ) AS sub_query;