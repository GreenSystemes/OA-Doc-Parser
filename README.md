# OpenApi Doc Parser

OpenApi Doc Parser can extract OpenApi from your PHP code. This program is in case of PHP code isn't generate from OpenApi specification. Why is it usefull :

 - You don't have to update an OpenApi file from a change in PHP code (vice-versa);
 - Simple usage;
 - Json PHP base extension is needed, no external dependencies at runtime. Just PHPUnit for tests;
 - Scan your PSR4 namespaces thanks to your `composer.json`;
 - OpenApi documentations must be write in *yaml* file format.
 
*Need PHP 7.4 to run ! Why ? Just to make the code bugless and more readable*.

Actually, the tool respond to our usage. But, you can make a PR if you see missing feature.

Features :

 - Can take a Swagger base file;
 - Create components;
 - Properties documentation of components can be add in class properties documentations;
 - Work with enum components;
 - Detect duplicates components;
 - Create routes;
 - Detect duplicate routes;
 - Make partial Swagger with properties flagged (partial swagger for some customers for example);
 - Configuration file.

## TODO

 - [ ] Make tests !
 - [ ] Check OpenApi `$ref` are valid.

## Usage

### Command line

    ./oa-doc-parser
            --composer ./path/to/your/composer.json
            --swagger-header ./path/to/your/swagger-header.yml
            --swagger-output ./path/to/your/output-swagger.yml

Required parameters :

 - `--composer` : Your composer file. Used to extract your PSR4 namespaces;
 - `--swagger-header` : Your Swagger PSR4 header file. See below for more information;
 - `--swagger-output` : File that will be write by this tool. If file exists, it will be overwrite.

Optional parameter :

 - `--tag` : Can be used more that one time. Define tag use filter component and route for partial swagger.

### Configuration file

    ./oa-doc-parser
            --conf ./path/to/your/OA-Doc-Parser.json

Your `OA-Doc-Parser.json` should be define as :

    {
        "composer": "./path/to/your/composer.json",
        "swagger": {
            "header": "./path/to/your/swagger-header.yml",
            "output": "./path/to/your/output-swagger.yml",
            "partial": [
                "TAG1", "TAG2"
            ]
        }
    }

Required parameters :

 - `composer` : Your composer file. Used to extract your PSR4 namespaces;
 - `swagger.header` : Your Swagger PSR4 header file. See below for more information;
 - `swagger.output`: File that will be write by this tool. If file exists, it will be overwrite.


Optional parameter :

 - `swagger.partial` : String array, define tag use filter component and route for partial swagger.

### Swagger header

Your `swagger-header.yml` must be write in *YAML* and need to contains :

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

#### @OA-Partial-Tags

List of tags used if partial tags are defined in configuration.

You can name you tags by respecting that regex `[A-Za-z0-9_-]`. Space separate tags

    /**
     * @OA-Partial-Tags CustomerApi AcmeCompanyApi
     */

### Examples

#### Controller

    <?php

    class UserController {
        /**
         * @OA-Method GET
         * @OA-Path /acme/users/{id}
         * @OA-Partial-Tags CustomerApi AcmeCompanyApi
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
