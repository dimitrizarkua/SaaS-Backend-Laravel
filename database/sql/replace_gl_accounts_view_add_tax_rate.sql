DROP VIEW gl_accounts_view;
CREATE OR REPLACE VIEW gl_accounts_view AS

SELECT gl_accounts.id                         as gl_account_id,
       gl_accounts.name                       as gl_account_name,
       gl_accounts.code                       as gl_account_code,
       locations.id                           as location_id,
       locations.name                         as location_name,
       account_types.id                       as account_type_id,
       account_types.name                     as account_type_name,
       account_types.increase_action_is_debit as is_debit,
       accounting_organizations.id            as accounting_organization_id,
       (CASE
          WHEN gl_accounts.bank_account_number IS NOT NULL THEN true
          ELSE false
         END)                                 AS is_bank_account,
       gl_accounts.enable_payments_to_account AS enable_payments_to_account,
       tax_rates.id                           AS tax_rate_id,
       tax_rates.name                         AS tax_rate_name,
       tax_rates.rate                         AS tax_rate_value
FROM gl_accounts
       JOIN accounting_organizations
            ON accounting_organizations.id = gl_accounts.accounting_organization_id

       JOIN accounting_organization_locations
            ON accounting_organization_locations.accounting_organization_id = accounting_organizations.id

       JOIN locations
            ON locations.id = accounting_organization_locations.location_id

       JOIN account_types
            ON account_types.id = gl_accounts.account_type_id

       JOIN tax_rates
            ON tax_rates.id = gl_accounts.tax_rate_id

WHERE accounting_organizations.is_active
  AND gl_accounts.is_active
