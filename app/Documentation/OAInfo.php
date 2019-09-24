<?php

//@codingStandardsIgnoreStart

/**
 * @OA\Info(
 *     @OA\Contact(
 *          name="API Support",
 *          email="development@steamatic.com.au"
 *      ),
 *     version="1.0.0",
 *     title="Steamatic API",
 *     description="
This document describes REST API exposed by Steamatic backend application. The API
is supposed to be used only by first-party clients (i.e. Steamatic apps) and not
supposed to be used or exposed to a third-party.

All endpoints are only accessible via https. When testing requests using this page,
please note that API is available in multiple environments, please make sure to select
correct server.

## Authentication & Authorization

The API uses [OAuth 2.0](https://oauth.net/2/) framework to secure access to the API.

Steamatic web applications should use the Password grant type.
Mobile applications use password_mobile as grant type for authorization (which is a Password grant type too by its nature).
In this flow, the user's username and password are exchanged directly for an access token.
Please refer to
[official documentation](https://oauth.net/2/grant-types/password/) for more information about
Password grant type.
Please use the instructions in
[this guide](https://www.oauth.com/oauth2-servers/access-tokens/password-grant/)
to obtain access token from authorization server. Please note you are not required to specify
client_id and client_secret in requests from Steamatic apps.

A user can be authorized via Office 365 account. Applications should use custom grant type social .
Mobile applications should use social_mobile grant type.
In order to authorize using Office 356 account, a request to /oauth/token endpoint should contain the following additional information:

```json
{
    ""grant_type"": ""social"",
    ""network"": ""office365"",
    ""access_token"": ""ACCESS_TOKEN_FROM_OFFICE365""
}
```

Issued access token is valid for 25 minutes, refresh token - for 2 hours.
Issued access and refresh tokens for mobile app (password_mobile grant type) are valid for 1 years.

Please refer to [this guide](https://www.oauth.com/oauth2-servers/access-tokens/refreshing-access-tokens/)
for instruction on how to refresh access tokens.

## REST

The Steamatic API is a RESTful API. This means that the API is designed to allow you
to get, create, update, & delete objects with the HTTP verbs GET, POST, PUT, PATCH, & DELETE.

## Cross-Origin Resource Sharing

The Steamatic API supports CORS for communicating from Javascript. You will need to set
an Origin URI when creating your application to allow for CORS to check the rules for your domain.

## Supported request & response formats

The Steamatic API speaks exclusively in **JSON**. This means that you should
always set the **Content-Type** and **Accept** header to `application/json` to ensure
that your requests are properly accepted and processed by the API. The exceptions are file upload endpoints,
please refer to documentation for that endpoints.

## Response Structure

### The Envelope

Every successful response is contained by an envelope.
That is, each response has a predictable set of keys with which you can expect to interact:
```json
{
    ""data"": {
        ...
    },
    ""pagination"": {
        ...
    }
}
```

#### DATA

The data key is the meat of the response. It may be a list or dictionary, but either way this is where you'll find the
data you requested.

#### PAGINATION

```json
""pagination"": {
    ""per_page"": 15,
    ""current_page"": 1,
    ""last_page"": 1,
    ""total_items"": 10
}
```

On endpoints where pagination is present, we support the following pagination
conditions as query parameters:
- `page`
- `per_page`

`page` indicates the number in sequence of pages (as of a book) you'd like
to receive. `per_page` is the number of items you'd like to receive per `page`.
Note that the default values should be fine for most cases - but if you
decide to increase this number there is a maximum  value defined on each
endpoint.

### Errors

Error responses will have a consistently formed JSON body. The keys may include:
<table class=""responses_definitions"">
    <tr>
        <th>Key</th>
        <th>Value</th>
    </tr>
    <tr>
        <td><b>status_code</b></td>
        <td>HTTP status code of the error.</td>
    </tr>
    <tr>
        <td><b>error_code</b></td>
        <td>Lowercased and underscored error code which corresponds to the client error code.</td>
    </tr>
    <tr>
        <td><b>error_message</b></td>
        <td>Human readable message which corresponds to the client error.</td>
    </tr>
    <tr>
        <td><b>fields</b> <i>(optional)</i></td>
        <td>A hash of fields names that have validation errors. See example below.</td>
    </tr>
    <tr>
        <td><b>data</b> <i>(optional)</i></td>
        <td>An optional data to be sent to the client with the error.</td>
    </tr>
    <tr>
        <td><b>reference_id</b> <i>(optional)</i></td>
        <td>Error reference identifier. Will only be set for unexpected fatal errors for debug purposes.</td>
    </tr>
</table>

### Error Response Example

Status-Code: 422 Invalid Request

```json
{
    ""status_code"": 422,
    ""error_code"": ""invalid_request"",
    ""error_message"": ""There are validation errors"",
    ""fields"": {
        ""first_name"": [
            ""This field is required"",
            ""First name must contain only letters and dots.""
        ],
        ""last_name"": [
            ""This field is required"",
            ""Last name must contain only letters and dots.""
        ],
        ""permissions.0"": [
            ""Permission doesn't exists""
        ],
        ""permissions.1"": [
            ""Permission doesn't exists""
        ]
    }
}
```

## HTTP Status Codes

<table class=""responses_definitions"">
    <tr>
        <th>Status Code</th>
        <th>Description</th>
    </tr>
    <tr>
        <td><b>200</b></td>
        <td>OK. Everything worked as expected.</td>
    </tr>
    <tr>
        <td><b>201</b></td>
        <td>Created. We will return a 201 after a successful POST where a resource was created.</td>
    </tr>
    <tr>
        <td><b>400</b></td>
        <td>Malformed request.</td>
    </tr>
    <tr>
        <td><b>401</b></td>
        <td>Unauthorized. The request requires user authentication (not logged in), provided credentials are not valid or authenticated user is not authorized to perform requested action.</td>
    </tr>
    <tr>
        <td><b>403</b></td>
        <td>Forbidden. User is either not allowed to do requested operation or doesn't have enough right to access requested resource.</td>
    </tr>
    <tr>
        <td><b>404</b></td>
        <td>Not found. Requested resource could not be found.</td>
    </tr>
    <tr>
        <td><b>405</b></td>
        <td>Not allowed. User is not allowed to do requested operation for some reason.</td>
    </tr>
    <tr>
        <td><b>409</b></td>
        <td>Conflict. A conflict needs to be resolved before the request can be made.</td>
    </tr>
    <tr>
        <td><b>422</b></td>
        <td>Invalid request. The request body is parse-able however with invalid content.</td>
    </tr>
    <tr>
        <td><b>429</b></td>
        <td>Too Many Requests. Exceeded the rate limit for requests.</td>
    </tr>
    <tr>
        <td><b>500</b></td>
        <td>Internal Server Error.</td>
    </tr>
    <tr>
        <td><b>503</b></td>
        <td>The backend server is not available.</td>
    </tr>
</table>
",
 * ),
 * @OA\Server(
 *      description="Development Server",
 *      url="https://api.dev.steamatic.com.au/v1",
 * ),
 * @OA\Server(
 *      description="Staging Server",
 *      url="https://api.staging.steamatic.com.au/v1",
 * ),
 * @OA\Server(
 *      description="Training Server",
 *      url="https://api.training.steamatic.com.au/v1",
 * ),
 * @OA\Server(
 *      description="Local Server",
 *      url="http://localhost:8000/v1",
 * ),
 */

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication-related operations",
 * )
 * @OA\Tag(
 *     name="Roles",
 *     description="Operations with roles",
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="Operations with users",
 * )
 * @OA\Tag(
 *     name="Teams",
 *     description="Operations with teams",
 * )
 * @OA\Tag(
 *     name="Tags",
 *     description="Operations with tags",
 * )
 * @OA\Tag(
 *     name="Documents",
 *     description="Operations with documents",
 * )
 * @OA\Tag(
 *     name="Locations",
 *     description="Operations with locations",
 * )
 * @OA\Tag(
 *     name="Addresses",
 *     description="Operations with addresses",
 * )
 * @OA\Tag(
 *     name="Meetings",
 *     description="Operations with meetings",
 * )
 * @OA\Tag(
 *     name="Notes",
 *     description="Operations with notes",
 * )
 * @OA\Tag(
 *     name="Messages",
 *     description="Operations with messages",
 * )
 * @OA\Tag(
 *     name="Contacts",
 *     description="Operations with contacts",
 * )
 * @OA\Tag(
 *     name="Jobs",
 *     description="Operations with jobs",
 * )
 * @OA\Tag(
 *     name="Photos",
 *     description="Operations with photos",
 * )
 * @OA\Tag(
 *     name="Management",
 *     description="System management operations",
 * )
 * @OA\Tag(
 *     name="Search",
 *     description="Search operations",
 * )
 * @OA\Tag(
 *     name="Finance",
 *     description="Finance operations",
 * )
 * @OA\Tag(
 *     name="Site Survey",
 *     description="Site Survey operations",
 * )
 * @OA\Tag(
 *     name="Operations",
 *     description="Scheduling operations",
 * )
 * @OA\Tag(
 *     name="Purchase Orders",
 *     description="Purchase orders operations",
 * )
 * @OA\Tag(
 *     name="Invoices",
 *     description="Invoices operations",
 * )
 * @OA\Tag(
 *     name="Credit Notes",
 *     description="Credit notes operations",
 * )
 * @OA\Tag(
 *     name="Reporting",
 *     description="Reporting operations",
 * )
 * @OA\Tag(
 *     name="Usage and Actuals",
 *     description="Usage and actuals operations",
 * )
 * @OA\Tag(
 *     name="Equipment",
 *     description="Equipment operations",
 * )
 *  @OA\Tag(
 *     name="Labours",
 *     description="Labours operations",
 * )
 * @OA\Tag(
 *     name="Materials",
 *     description="Materials operations",
 * )
 * @OA\Tag(
 *     name="Assessment Reports",
 *     description="Assessment reports operations",
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     required={"per_page","current_page","last_page"},
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         description="Total number of items on pages. By default it is equals to 15",
 *         example=15
 *     ),
 *     @OA\Property(
 *         property="current_page",
 *         type="integer",
 *         description="Current page number",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="last_page",
 *         type="integer",
 *         description="Last page number",
 *         example=100
 *     ),
 *     @OA\Property(
 *         property="total_items",
 *         type="integer",
 *         description="Total items",
 *         example=100
 *     ),
 * )
 * @OA\Parameter(
 *     parameter="required-id-in-path",
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="Requested object identifier",
 *     @OA\Schema(
 *         type="integer",
 *         example=1,
 *     )
 * ),
 *
 */

// @codingStandardsIgnoreEnd
