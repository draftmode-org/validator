# Terrazza/Validator
This component validates/approves content against schemas

## _Object/Classes_

1. [ObjectValueSchema](#object-schema)
2. [ObjectValidator](#object-validator)

<a id="object-schema" name="object-schema"></a>
<a id="user-content-object-schema" name="user-content-object-schema"></a>
### ObjectValueSchema
Properties:
- name (string, required)
- type (string, required)
  - number
  - integer
  - double
  - array
  - boolean
  - string
  - object
- required (bool, default=false)
- nullable (bool, default=false)
- patterns (string, optional)
<br>_only used for type: string_
- format (string, optional)
<br>_only used for type: string_
- minLength (int, optional)
<br>_only used for type: number, integer, double_ 
- maxLength (int, optional)
  <br>_only used for type: number, integer, double_
- minItems (int, optional)
<br>_only used for type: array_
- maxItems (int, optional)
<br>_only used for type: array_
- minRange (float, optional)
<br>_only used for type: number, integer, double_
- maxRange (float, optional)
<br>_only used for type: number, integer, double_
- multipleOf (float, optional)
<br>_only used for type: number, integer, double_
- childSchemas (arrayOf ObjectValueSchema, optional)

<a id="object-validator" name="object-validator"></a>
<a id="user-content-object-validator" name="user-content-validator"></a>
### ObjectValidator
#### method: isValid
#### method: validate
#### method: getEncodedValue