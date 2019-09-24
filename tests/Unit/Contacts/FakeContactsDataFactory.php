<?php

namespace Tests\Unit\Contacts;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Models\CompanyData;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\PersonData;
use Faker\Factory as Faker;

/**
 * Class FakeContactsDataFactory
 *
 * @package Tests\Unit\Contacts
 */
class FakeContactsDataFactory
{

    /**
     * Returns person instance.
     *
     * @return \App\Components\Contacts\Models\PersonData
     *
     * @throws \JsonMapper_Exception
     */
    public static function getPersonDataInstance()
    {
        $faker = Faker::create();

        $person   = new PersonData();
        $category = ContactCategory::query()
            ->where('type', '!=', ContactCategoryTypes::CUSTOMER)
            ->first();

        $person->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setContactCategoryId($category->id);

        return $person;
    }

    /**
     * Returns person instance where all fields was filled.
     *
     * @return \App\Components\Contacts\Models\PersonData
     *
     * @throws \JsonMapper_Exception
     */
    public static function getFullPersonDataInstance()
    {
        $faker = Faker::create();

        $person = self::getPersonDataInstance();
        $person->setDirectPhone($faker->phoneNumber)
            ->setMobilePhone($faker->phoneNumber)
            ->setJobTitle($faker->title)
            ->setEmail($faker->email);

        return $person;
    }

    /**
     * Returns person instance where contact_category_id = CUSTOMER.
     *
     * @return \App\Components\Contacts\Models\PersonData
     * @throws \JsonMapper_Exception
     */
    public static function getCustomerDataInstance()
    {
        $faker = Faker::create();

        $customer = new PersonData();
        $category = ContactCategory::query()
            ->where('type', '=', ContactCategoryTypes::CUSTOMER)
            ->first();

        $customer->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setContactCategoryId($category->id);

        return $customer;
    }

    /**
     * Returns company instance.
     *
     * @return \App\Components\Contacts\Models\CompanyData
     *
     * @throws \JsonMapper_Exception
     */
    public static function getCompanyDataInstance()
    {
        $faker = Faker::create();

        $company  = new CompanyData();
        $category = ContactCategory::query()->first();

        $company->setAbn($faker->regexify('[0-9]{11}'))
            ->setLegalName($faker->name)
            ->setDefaultPaymentTermsDays($faker->numberBetween(1, 365))
            ->setContactCategoryId($category->id);

        return $company;
    }

    /**
     * Returns company instance where all fields was filled.
     *
     * @return \App\Components\Contacts\Models\CompanyData
     *
     * @throws \JsonMapper_Exception
     */
    public static function getFullCompanyDataInstance()
    {
        $faker = Faker::create();

        $company = self::getCompanyDataInstance();
        $company->setWebsite($faker->url)
            ->setTradingName($faker->name)
            ->setEmail($faker->email)
            ->setBusinessPhone($faker->phoneNumber);

        return $company;
    }
}
