CREATE OR REPLACE FUNCTION set_primary_location() RETURNS TRIGGER AS $$
DECLARE
  helper_location_id bigint;
BEGIN
  IF    TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
    IF NEW."primary" = true THEN
      UPDATE location_user SET "primary" = false WHERE user_id = NEW.user_id AND location_id != NEW.location_id;
    ELSIF NOT EXISTS (SELECT 1 FROM location_user WHERE user_id = NEW.user_id AND "primary" = true LIMIT 1) THEN
      UPDATE location_user SET "primary" = true WHERE user_id = NEW.user_id AND location_id = NEW.location_id;
    END IF;
    RETURN NEW;
  ELSIF TG_OP = 'DELETE' THEN
    helper_location_id := (SELECT location_id FROM location_user WHERE user_id = OLD.user_id LIMIT 1);
    IF NOT EXISTS (SELECT 1 FROM location_user WHERE user_id = OLD.user_id AND "primary" = true LIMIT 1) THEN
      UPDATE location_user SET "primary" = true WHERE user_id = OLD.user_id AND location_id = helper_location_id;
    END IF;
    RETURN OLD;
  END IF;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER verify_primary_location_for_user
  AFTER UPDATE OR INSERT OR DELETE ON location_user
  FOR EACH ROW
EXECUTE PROCEDURE set_primary_location();
