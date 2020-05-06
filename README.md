# OpenApi Doc Parser

*Need PHP 7.4 to run !*

## TODO

 - [ ] Make tests !

## Usage


    ./oa-doc-parser
            --composer ./path/to/your/composer.json
            --swagger-header ./path/to/your/swagger-header.yml
            --swagger-output ./path/to/your/output-swagger.yml

Your `swagger-header.yml` need to contains :

 - Open Api version
 - `info` object
 - `servers` object

[Open Api Documentation here](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.3.md)

Like that for example :

    openapi: 3.0.0
    info:
      title: Your title
      description: Api to interact with the Green Solution
      version: 0.1.29
    servers:
      - url: 'https://server.acme'
    tags:
      - name: Users
        description: Manage Users
      - name: User Things
        description: Manage User thing

## Commenting your code

### Component annotations

#### @OA-Name

Name of component, aka. PHP object name. Should use a constructor comment.

    /**
     * @OA-Name MyObjectName
     */

#### @OA-Component-Begin and @OA-Component-End

`yaml` of [Component object](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.3.md#componentsObject)

Use to describe object. Should use a constructor comment.

**DO NOT PUT PROPERTIES KEY !**

    /**
     * @OA-Component-Begin
     * type: object
     * description: Object usefull to ....
     * example:
     *   id: 42
     *   name: 'Dev team'
     *   quota: 3.14
     *   period: 2
     * @OA-Component-End
     */


#### @OA-Property-Begin and @OA-Property-End

Use to describe each property of object. Should use a property comment.

    /**
     * @OA-Property-Begin
     * propertyNameYouWant:
     *   type: integer
     *   description: That property do ...
     * @OA-Property-End
     */


### Path annotations

All of path annotations should be add in method comment.

#### @OA-Method

Method used on that method. Values can be one of them : `GET`, `POST`, `DELETE`, `PUT`, `OPTIONS`, `HEAD`, `PATCH` or `TRACE`

    /**
     * @OA-Method POST
     */

#### @OA-Path

Path for that method

    /**
     * @OA-Path /acme/users/{id}/foo
     */

#### @OA-Path-Begin and @OA-Path-End

`yaml` of [Operation object](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.3.md#operationObject)

    /**
     * @OA-Path-Begin
     * tags:
     * - Users
     * summary: Updates a user in the store with form data
     * operationId: updateUserWithForm
     * parameters:
     * - name: userId
     *   in: path
     *   description: ID of user that needs to be updated
     *   required: true
     *   schema:
     *     type: string
     * requestBody:
     *   content:
     *     'application/x-www-form-urlencoded':
     *     schema:
     *     properties:
     *       name: 
     *         description: Updated name of the user
     *         type: string
     *       status:
     *         description: Updated status of the user
     *         type: string
     *     required:
     *       - status
     * responses:
     *   '200':
     *     description: User updated.
     *     content: 
     *       'application/json': {}
     *       'application/xml': {}
     * @OA-Path-End
     */

### Examples

#### Controller

    <?php

    class UserController {
        /**
         * @OA-Method GET
         * @OA-Path /acme/users/{id}
         * @OA-Path-Begin
         * tags:
         * - Users
         * summary: Get a user in the store
         * operationId: getUser
         * parameters:
         * - name: userId
         *   in: path
         *   description: ID of user that needs to be updated
         *   required: true
         *   schema:
         *     type: integer
         * responses:
         *   '200':
         *     description: User data
         *     content: 
         *       'application/json': {}
         *       'application/xml': {}
         * @OA-Path-End
         */
        public function getUserAction( Request $request, Responses $response, array $args ) : Response {
            //Some things
        }
    }

#### Objects

##### Common

    <?php

    /**
     * @OA-Name MyObject
     * @OA-Component-Begin
     * type: object
     * description: Object usefull to ....
     * example:
     *   id: 42
     *   name: 'Dev team'
     *   quota: 3.14
     *   period: 2
     * @OA-Component-End
     */
    class MyObject {
        /**
         * @OA-Property-Begin
         * id:
         *   type: integer
         *   description: Global ID
         * @OA-Property-End
         */
        private int $id;

        /**
         * @OA-Property-Begin
         * name:
         *   type: string
         *   description: Name of user
         * @OA-Property-End
         */
        private string $name;

        /**
         * @OA-Property-Begin
         * quota:
         *   type: float
         *   description: Quota of actions
         * @OA-Property-End
         */
        private float $quota;
    }

##### Enum

    <?php

    /**
     * @OA-Name MyEnumObject
     * @OA-Component-Begin
     * type: integer
     * nullable: true
     * enum:
     *   - 0
     *   - 1
     *   - 2
     * description: >
     *   Your super description
     *    * `0` **UNDEFINED** : No quota rule;
     *    * `1` **HALF** : Quota limited to week (Monday to Sunday);
     *    * `2` **FULL** : Quota limited to month (1st to 28th/29th/30th/31th);
     * @OA-Component-End
     */
    class MyEnum {
        //Some things
    }