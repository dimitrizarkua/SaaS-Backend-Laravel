<?php

namespace App\Rules;

use App\Components\Contacts\Models\Contact;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class ContactCategory
 *
 * @package App\Rules
 */
class ContactCategory implements Rule
{
    /**
     * Contact category.
     *
     * @var string
     */
    private $categoryType;

    /**
     * BelongsToAccountingOrganization constructor.
     *
     * @param string $categoryType
     */
    public function __construct(string $categoryType)
    {
        $this->categoryType = $categoryType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $contact = Contact::find($value);

        return $contact
            && $contact->category->type === $this->categoryType;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return sprintf('The contact category must be \'%s\'', $this->categoryType);
    }
}
