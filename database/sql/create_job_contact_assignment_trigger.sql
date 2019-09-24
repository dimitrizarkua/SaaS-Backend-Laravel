CREATE OR REPLACE FUNCTION update_invoice_to_field_for_job_contact_assignments() RETURNS TRIGGER AS $$
DECLARE
  helper_location_id bigint;
BEGIN
  IF    TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
    IF NEW."invoice_to" = true THEN
      UPDATE job_contact_assignments SET "invoice_to" = false WHERE job_id = NEW.job_id AND assignee_contact_id != NEW.assignee_contact_id;
    ELSIF NOT EXISTS (SELECT 1 FROM job_contact_assignments WHERE job_id = NEW.job_id AND "invoice_to" = true LIMIT 1) THEN
      UPDATE job_contact_assignments SET "invoice_to" = true WHERE job_id = NEW.job_id AND assignee_contact_id = NEW.assignee_contact_id;
    END IF;
    RETURN NEW;
  END IF;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER verify_set_invoice_to_field
  AFTER UPDATE OR INSERT ON job_contact_assignments
  FOR EACH ROW
EXECUTE PROCEDURE update_invoice_to_field_for_job_contact_assignments();
