Jira Rest Gush Adapter Library
==============================

Plugs [Jira](https://www.atlassian.com/software/jira/overview) [REST API](https://developer.atlassian.com/jira/docs/latest/reference/rest-api.html) into Gush as an adapter.


```yaml
# app/config/config.yml
    jira_api:
        url:         "http://jira.your-organisation.com/jira/rest/api/latest/"
        credentials: "username:password"
```

Usage
-----

```php
// Get a particular Jira issue from the Gush\Service\IssueService
$issueService = $this->get('jira_api.issue');
$issueService->get('STORY-KEY');

// Get all issues by a project in the Gush\Service\ProjectService
$projectService = $this->get('jira_api.project');
$projectService->getAll();

// Search for a issue in the Gush\Service\SearchService
$searchService = $this->get('jira_api.search');
$searchService->search(
    array(
        'jql' => 'assignee=fred+order+by+duedate',
    )
);
```

note: inspired from https://github.com/MedicoreNL/JiraApiBundle
