<?php

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Models\CompanyData;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\VO\CreateAccountingOrganizationData;
use App\Components\Locations\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

/**
 * Class ImportLocationAndContactSeeder
 */
class ImportLocationAndContactSeeder extends Seeder
{
    private const TRADING_NAME        = 'Steamatic';
    private const COUNTRY_CODE        = 'AU';
    private const PAYMENT_TERM        = 30;
    private const TEST_API_SECRET_KEY = 'tuad0-6maWzAcDmmbzP6Nw';

    // ALL NEW LOCATIONS TO THE END!!!
    private static $locations = [
        ['ACT', 'Canberra', 600],
        ['ADE', 'Adelaide', 570],
        ['ALB', 'Albury-Wodonga', 600],
        ['BBY', 'Batemans Bay', 600],
        ['BEN', 'Bendigo', 600],
        ['BLR', 'Ballarat', 600],
        ['BNE', 'Brisbane', 600],
        ['BNK', 'Ballina', 600],
        ['CCO', 'Central Coast', 600],
        ['CFS', 'Coffs Harbour', 600],
        ['GEL', 'Geelong', 600],
        ['GOL', 'Gold Coast', 600],
        ['GPS', 'Gippsland', 600],
        ['HOR', 'Horsham', 600],
        ['MEL', 'Melbourne', 600],
        ['NWC', 'Newcastle', 600],
        ['PMQ', 'Port Macquarie', 600],
        ['SHP', 'Shepparton', 600],
        ['SSC', 'Sunshine Coast', 600],
        ['SYD', 'Sydney', 600],
        ['TAS', 'Tasmania', 600],
        ['WAGG', 'Wagga Wagga', 600],
        ['WARR', 'Warrnambool', 600],
        ['WOL', 'Wollongong', 600],
        ['YEA', 'Yea', 600],
        ['CWT', 'Central West', 525],
        ['TSV', 'Townsville', 600],
        ['BEG', 'Bega', 600],
        ['MIL', 'Mildura Swan Hill', 570],
        ['TAM', 'Tamworth', 600],
        ['ADL', 'AdelaideOld', 570],
    ];

    private static $contacts = [
        [
            'legal_name'     => 'Fischers Cleaning Pty Ltd',
            'abn'            => '65 005 042 642',
            'email'          => 'contracts@stematic.com.au',
            'business_phone' => '03 9587 6333',
            'address'        => '10-11 Walker Street',
            'suburb'         => 'Braeside',
            'state'          => 'VIC',
            'postcode'       => '3195',
            'locations'      => [1, 6, 14, 19, 20],
        ],
        [
            'legal_name'     => 'ACT Recovery Services Pty Ltd',
            'abn'            => '70 626 060 776',
            'email'          => 'canberra@steamatic.com.au',
            'business_phone' => '02 6242 0856',
            'address'        => '2/38 Dacre Street',
            'suburb'         => 'Mitchell',
            'state'          => 'ACT',
            'postcode'       => '2911',
            'locations'      => [0, 3, 23, 29],
        ],
        [
            'legal_name'     => 'Argentin Pty Ltd',
            'abn'            => '31 069 630 388',
            'email'          => 'shepparton@steamatic.com.au',
            'business_phone' => '03 5822 0022',
            'address'        => 'PO Box 5849',
            'suburb'         => 'Shepparton',
            'state'          => 'VIC',
            'postcode'       => '3630',
            'locations'      => [2, 21],
        ],
        [
            'legal_name'     => 'Epsom Investment Fund Pty Ltd',
            'abn'            => '91 154 651 028',
            'email'          => 'bendigo.accounts@steamatic.com.au',
            'business_phone' => '03 5448 5200',
            'address'        => '4 Harrien Court',
            'suburb'         => 'Epsom',
            'state'          => 'VIC',
            'postcode'       => '3551',
            'locations'      => [4, 17, 28],
        ],
        [
            'legal_name'     => 'Pandamich Pty Ltd',
            'abn'            => '22 843 971 033',
            'email'          => 'ballarat@steamatic.com.au',
            'business_phone' => '03 5339 4650',
            'address'        => 'Factory 4 / 31 Grandlee Drive',
            'suburb'         => 'Wendouree',
            'state'          => 'VIC',
            'postcode'       => '3355',
            'locations'      => [5, 13],
        ],
        [
            'legal_name'     => 'Hilraft Pty Ltd',
            'abn'            => '93 003 142 934',
            'email'          => 'northernrivers@steamatic.com.au',
            'business_phone' => '02 6624 5682',
            'address'        => '3 Roy Place',
            'suburb'         => 'Richmond Hill',
            'state'          => 'NSW',
            'postcode'       => '2480',
            'locations'      => [7],
        ],
        [
            'legal_name'     => 'P & P Johnson Investments Pty Ltd',
            'abn'            => '87 130 340 831',
            'email'          => 'newcastle@steamatic.com.au',
            'business_phone' => '1300 783 262',
            'address'        => 'PO Box 511',
            'suburb'         => 'East Maitland',
            'state'          => 'NSW',
            'postcode'       => '2323',
            'locations'      => [8, 15],
        ],
        [
            'legal_name'     => 'Rochlomo Pty Ltd',
            'abn'            => '95 424 431 784',
            'email'          => 'geelong@steamatic.com.au',
            'business_phone' => '03 5241 6333',
            'address'        => '7 Essington Street',
            'suburb'         => 'Grovedale',
            'state'          => 'VIC',
            'postcode'       => '3216',
            'locations'      => [10, 22],
        ],
        [
            'legal_name'     => 'Bentley\'s Restoration Group',
            'abn'            => '48 128 939 480',
            'email'          => 'coffsharbour@steamatic.com.au',
            'business_phone' => '02 6652 1253',
            'address'        => 'Unit 3/2 O\'Keefe Drive',
            'suburb'         => 'Coffs Harbour',
            'state'          => 'NSW',
            'postcode'       => '2450',
            'locations'      => [9, 16],
        ],
        [
            'legal_name'     => 'Connor Angel Pty Ltd',
            'abn'            => '60 413 221 562',
            'email'          => 'sbrown@steamatic.com.au',
            'business_phone' => '07 5689 1577',
            'address'        => '32 Horizon Avenue',
            'suburb'         => 'Ashmore',
            'state'          => 'QLD',
            'postcode'       => '4214',
            'locations'      => [11, 18],
        ],
        [
            'legal_name'     => 'E & H Family Trust',
            'abn'            => '44 049 037 412',
            'email'          => 'gippsland@steamatic.com.au',
            'business_phone' => '03 5133 9341',
            'address'        => 'PO Box 3020',
            'suburb'         => 'Gippsland Mc',
            'state'          => 'VIC',
            'postcode'       => '3841',
            'locations'      => [12],
        ],
        [
            'legal_name'     => 'Anna L Hamilton',
            'abn'            => '47 885 801 964',
            'email'          => 'yea@steamatic.com.au',
            'business_phone' => '03 5797 2555',
            'address'        => '5663 Whittlesea Rd',
            'suburb'         => 'Yea',
            'state'          => 'VIC',
            'postcode'       => '3717',
            'locations'      => [24],
        ],
        [
            'legal_name'     => 'Nartac Pty Ltd',
            'abn'            => '62 311 037 419',
            'email'          => 'centralwest@steamatic.com.au',
            'business_phone' => '02 6394 6242',
            'address'        => '2/10 Scott Place',
            'suburb'         => 'Orange',
            'state'          => 'NSW',
            'postcode'       => '2800',
            'locations'      => [25],
        ],
        [
            'legal_name'     => 'Ohap Pty Ltd Atf Marment Family Trust',
            'abn'            => '73 722 127 869',
            'email'          => 'townsville@steamatic.com.au',
            'business_phone' => '07 4728 1580',
            'address'        => 'Unit 8, 72-78 Crocodile Crescent, Mount St John',
            'suburb'         => 'Townsville',
            'state'          => 'QLD',
            'postcode'       => '4814',
            'locations'      => [26],
        ],
        [
            'legal_name'     => 'Steamatic of Bega Region Pty Ltd',
            'abn'            => '23 623 622 935',
            'email'          => 'bega@steamatic.com.au',
            'business_phone' => '0419 280 949',
            'address'        => '27 Elizabeth Parade',
            'suburb'         => 'Tura Beach',
            'state'          => 'NSW',
            'postcode'       => '2548',
            'locations'      => [27],
        ],
    ];

    /**
     * Seeds the account type groups.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function run()
    {
        /** @var ContactsServiceInterface $contactsService */
        $contactsService = app()->make(ContactsServiceInterface::class);

        /** @var AccountingOrganizationsServiceInterface $accountingOrganizationsService */
        $accountingOrganizationsService = app()->make(AccountingOrganizationsServiceInterface::class);

        DB::transaction(function () use ($contactsService, $accountingOrganizationsService) {
            $category = ContactCategory::query()
                ->where(['type' => ContactCategoryTypes::COMPANY_LOCATION])
                ->firstOrFail();

            $country = Country::query()
                ->where(['iso_alpha2_code' => self::COUNTRY_CODE])
                ->firstOrFail();

            foreach (self::$contacts as $contact) {
                $state = State::firstOrCreate([
                    'code'       => $contact['state'],
                    'country_id' => $country->id,
                ]);

                $suburb = Suburb::firstOrCreate([
                    'state_id' => $state->id,
                    'name'     => $contact['suburb'],
                    'postcode' => $contact['postcode'],
                ]);

                $address = Address::create([
                    'suburb_id'      => $suburb->id,
                    'contact_name'   => $contact['legal_name'],
                    'address_line_1' => $contact['address'],
                ]);

                $company = $contactsService->createCompany(new CompanyData([
                    'trading_name'               => self::TRADING_NAME,
                    'default_payment_terms_days' => self::PAYMENT_TERM,
                    'contact_category_id'        => $category->id,
                    'email'                      => $contact['email'],
                    'business_phone'             => $contact['business_phone'],
                    'legal_name'                 => $contact['legal_name'],
                    'abn'                        => $contact['abn'],
                ]));

                $contactsService->addAddress($company->id, $address->id, AddressContactTypes::MAILING);

                $accountingOrgData             = new CreateAccountingOrganizationData();
                $accountingOrgData->contact_id = $company->id;

                if (!App::environment(['production'])) {
                    $accountingOrgData->cc_payments_api_key = self::TEST_API_SECRET_KEY;
                }

                $organization = $accountingOrganizationsService->create($accountingOrgData);

                foreach ($contact['locations'] as $locationIndex) {
                    list($code, $name, $tzOffset) = self::$locations[$locationIndex];

                    $location = Location::firstOrCreate([
                        'code'      => $code,
                        'name'      => $name,
                        'tz_offset' => $tzOffset,
                    ]);

                    $accountingOrganizationsService->addLocation($organization->id, $location->id);
                }
            }
        });
    }
}
