-- The seed of this module carried the Thelia 2 wording of its message subject, which Thelia 3
-- renders with Twig: a shop installed before 3.0.1 mails the placeholder itself to the customer.
--
-- Only the subject is rewritten. The bodies are whole Smarty templates, loops and all, and
-- porting them to Twig is a separate job: a placeholder rewritten inside them would read as
-- working Twig while the loop around it still does nothing.

UPDATE `message_i18n`
INNER JOIN `message` ON `message`.`id` = `message_i18n`.`id`
SET `message_i18n`.`subject` = REPLACE(`message_i18n`.`subject`, '{$order_ref}', '{{ order_ref }}')
WHERE `message`.`name` = 'order_confirmation_dpdclassic';
