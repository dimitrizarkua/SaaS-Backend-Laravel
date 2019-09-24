CREATE OR REPLACE FUNCTION update_users_and_teams_view() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = current_timestamp;
    RETURN NEW;
END ;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_users_and_teams_view_trigger
    INSTEAD OF UPDATE ON users_and_teams_view
    FOR EACH ROW EXECUTE PROCEDURE update_users_and_teams_view();