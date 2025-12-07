I've identified the API endpoints for you. Since you've already added the new columns to the `users` table, the existing endpoints should now include this updated information.

Here are the endpoints:

### Profile Endpoint

*   **Endpoint:** `GET /api/user`
*   **Method:** `GET`
*   **Description:** This endpoint retrieves the profile information for the currently authenticated user. It's what you should use for a "profile page".
*   **Authentication:** Requires the user to be authenticated via Sanctum (the API token).

### All Users Endpoint

*   **Endpoint:** `GET /api/users`
*   **Method:** `GET`
*   **Description:** This endpoint retrieves a list of all users in the system.
*   **Authentication:** Requires the user to be an administrator and authenticated via Sanctum.

Both of these endpoints will return the `User` object(s) with all the visible attributes, including `first_name`, `surname`, `phone_number`, `department`, `position`, and `role`.

### Registration Endpoint

*   **Endpoint:** `POST /api/register`
*   **Method:** `POST`
*   **Description:** This endpoint allows new users to register. On success, it returns the new user's details and an API token.
*   **Authentication:** None required.

#### Required Inputs:

The following fields must be sent in the JSON body of the request:

| Field                   | Type    | Description                                                                 |
| ----------------------- | ------- | --------------------------------------------------------------------------- |
| `first_name`            | string  | The user's first name.                                                      |
| `surname`               | string  | The user's last name.                                                       |
| `email`                 | string  | A unique, valid email address.                                              |
| `phone_number`          | string  | The user's phone number.                                                    |
| `department`            | string  | The user's department.                                                      |
| `password`              | string  | The user's password (must meet security requirements and be confirmed).     |
| `password_confirmation` | string  | Must match the `password` field.                                            |
| `middle_name`           | string  | (Optional) The user's middle name.                                          |
| `terms`                 | boolean | (Optional, depends on config) Set to `true` to accept terms and conditions. |