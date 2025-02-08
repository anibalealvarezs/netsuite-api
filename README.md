# NetSuite API

## Instructions

Require the package in the `composer.json` file of your project, and map the package in the `repositories` section.
You must also map the `api-skeleton` package and its dependencies.

```json
{
    "require": {
        "php": ">=8.1",
        "anibalealvarezs/netsuite-api": "@dev"
    },
    "repositories": [
        {
            "type": "composer", "url": "https://satis.anibalalvarez.com/"
        }
    ]
}
```

Note: In order to use the package from GitLab, you need to have a valid SSH key configured in your GitLab account.

## Methods

- ### test: *Void*

  `Performs a test connection. Returns true if successful. Otherwise, throws an exception.`

  <details>
    <summary><strong>Parameters</strong></summary>
    No parameters required.
  </details><br>

- ### getSalesOrders: *Array*

  `Returns a list of Sales Orders.`

  <details>
    <summary><strong>Parameters</strong></summary>

  - Optional

    - `offset`: *Integer*  
      Number of Offset pages. Default is 0.
    - `limit`: *Integer*  
      Number between 1 and 1000. Default is 1000.
  </details><br>

- ### getSuiteQLQuery: *Array*

  `Performs a SuiteQL query.`

  <details>
    <summary><strong>Parameters</strong></summary>

  - Required

    - `query`: *String*  
      SuiteQL query to be performed.

  - Optional

    - `offset`: *Integer*  
      Number of Offset pages. Default is 0.
    - `limit`: *Integer*  
      Number between 1 and 1000. Default is 1000.
  </details><br>

- ### getSuiteQLQueryAll: *Array*

  `Performs a loop over a SuiteQL query to get all rows.`

  <details>
    <summary><strong>Parameters</strong></summary>

  - Required

    - `query`: *String*  
      SuiteQL query to be performed.

  - Optional

    - `limit`: *Integer*  
      Number between 1 and 1000. Default is 1000.
  </details><br>
