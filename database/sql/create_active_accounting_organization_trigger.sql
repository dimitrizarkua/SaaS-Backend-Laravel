CREATE OR REPLACE FUNCTION deactivate_accounting_organization()
  RETURNS TRIGGER AS $$
DECLARE
  helper_is_active boolean;
BEGIN
  IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
  THEN
    helper_is_active := (SELECT is_active FROM accounting_organizations WHERE id = NEW.accounting_organization_id);
    IF helper_is_active = true
    THEN
      UPDATE accounting_organizations
      SET "is_active" = false
      WHERE id IN (SELECT accounting_organization_id
                   FROM accounting_organization_locations
                   WHERE location_id = NEW.location_id)
        AND id != NEW.accounting_organization_id;
    END IF;
    RETURN NEW;
  END IF;
END;

$$ LANGUAGE plpgsql;

CREATE TRIGGER active_accounting_organization_trigger
  AFTER UPDATE OR INSERT
  ON accounting_organization_locations
  FOR EACH ROW
EXECUTE PROCEDURE deactivate_accounting_organization();
