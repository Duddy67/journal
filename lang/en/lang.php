<?php

return [
    'plugin' => [
        'name' => 'Journal',
        'description' => 'A simple plugin used to manage articles.'
    ],
    'journal' => [
      'articles' => 'Articles',
      'categories' => 'Categories',
      'fields' => 'Fields',
      'tab' => 'Journal',
      'access_articles' => 'Manage the articles',
      'access_categories' => 'Manage the article categories',
      'access_publish' => 'Allowed to publish articles',
      'access_delete' => 'Allowed to delete articles',
      'access_other_articles' => 'Manage other users articles',
      'manage_settings' => 'Manage Journal settings',
    ],
    'articles' => [
      'new_article' => 'New article',
      'filter_category' => 'Category',
      'filter_date' => 'Date',
      'filter_status' => 'Status',
      'filter_field_group' => 'Field group',
      'reorder' => 'Reorder Articles',
      'return_to_articles' => 'Return to the article list',
    ],
    'article' => [
      'title_placeholder' => 'New article title',
      'name_placeholder' => 'New article name',
      'slug_placeholder' => 'new-article-slug',
      'tab_categories' => 'Categories',
      'tab_fields' => 'Fields',
      'categories_comment' => 'Select categories for the article',
      'categories_placeholder' => 'There are no categories, you should create one first!',
    ],
    'categories' => [
      'reorder' => 'Reorder Categories',
      'return_to_categories' => 'Return to the article category list',
    ],
    'category' => [
      'name_placeholder' => 'New category name',
      'slug_placeholder' => 'new-category-slug'
    ],
    'fields' => [
      'filter_type' => 'Type',
      'filter_group' => 'Group',
      'groups' => 'Groups',
      'deletion_confirmation' => 'The selected fields are may be used in one or more articles.'.PHP_EOL.'All data linked to these fields will be lost.'.PHP_EOL.'Are you sure you want to delete them ?',
    ],
    'field' => [
      'text' => 'Text',
      'textarea' => 'Text area',
      'list' => 'List',
      'radio' => 'Radio',
      'checkbox' => 'Checkbox',
      'date' => 'Date',
      'datetime' => 'Datetime',
      'default_value' => 'Default value',
      'groups' => 'Groups',
      'deletion_confirmation' => 'This field is may be used in one or more articles.'.PHP_EOL.'All data linked to this field will be lost.'.PHP_EOL.'Are you sure you want to delete it ?',
    ],
    // Boilerplate attributes.
    'attribute' => [
      'title' => 'Title',
      'name' => 'Name',
      'slug' => 'Slug',
      'type' => 'Type',
      'code' => 'Code',
      'required' => 'Required',
      'yes' => 'Yes',
      'no' => 'No',
      'description' => 'Description',
      'title_placeholder' => 'New item title',
      'name_placeholder' => 'New item name',
      'code_placeholder' => 'New item code',
      'created_at' => 'Created at',
      'created_by' => 'Created by',
      'updated_at' => 'Updated at',
      'updated_by' => 'Updated by',
      'tab_edit' => 'Edit',
      'tab_manage' => 'Manage',
      'status' => 'Status',
      'published_up' => 'Start publishing',
      'published_down' => 'Finish publishing',
      'access' => 'Access',
      'viewing_access' => 'Viewing access',
      'category' => 'Category',
      'field_group' => 'Field group',
      'main_category' => 'Main category',
      'parent_category' => 'Parent category',
      'none' => 'None',
    ],
    'status' => [
      'published' => 'Published',
      'unpublished' => 'Unpublished',
      'trashed' => 'Trashed',
      'archived' => 'Archived'
    ],
    'action' => [
      'new' => 'New Article',
      'publish' => 'Publish',
      'unpublish' => 'Unpublish',
      'trash' => 'Trash',
      'archive' => 'Archive',
      'delete' => 'Delete',
      'save' => 'Save',
      'save_and_close' => 'Save and close',
      'create' => 'Create',
      'create_and_close' => 'Create and close',
      'cancel' => 'Cancel',
      'check_in' => 'Check-in',
      'select' => '- Select -',
      'publish_success' => ':count item(s) successfully published.',
      'unpublish_success' => ':count item(s) successfully unpublished.',
      'archive_success' => ':count item(s) successfully archived.',
      'trash_success' => ':count item(s) successfully trashed.',
      'delete_success' => ':count item(s) successfully deleted.',
      'check_in_success' => ':count item(s) successfully checked-in.',
      'parent_item_unpublished' => 'Cannot publish this item as its parent item is unpublished.',
      'previous' => 'Previous',
      'next' => 'Next',
      'deletion_confirmation' => 'Are you sure you want to delete the selected items ?',
      'cannot_reorder' => 'Cannot reorder items by category as none or more than 1 categories are selected. Please select only 1 category.',
      'checked_out_item' => 'The ":name" item cannot be modified as it is currently checked out by a user.',
      'check_out_do_not_match' => 'The user checking out doesn\'t match the user who checked out the item. You are not permitted to use that link to directly access that page.',
      'editing_not_allowed' => 'You are not allowed to edit this item.',
      'used_as_main_category' => 'The ":name" category cannot be deleted as it is used as main category in one or more articles.',
      'not_allowed_to_modify_item' => 'You are not allowed to modify the ":name" item.',
    ],
    'sorting' => [
        'title_asc' => 'Title (ascending)',
        'title_desc' => 'Title (descending)',
        'created_asc' => 'Created (ascending)',
        'created_desc' => 'Created (descending)',
        'updated_asc' => 'Updated (ascending)',
        'updated_desc' => 'Updated (descending)',
        'published_asc' => 'Published (ascending)',
        'published_desc' => 'Published (descending)',
        'order_asc' => 'Order by category (ascending)',
        'order_desc' => 'Order by category (descending)',
        'random' => 'Random'
    ],
    'settings' => [
      'category_title' => 'Category List',
      'category_description' => 'Displays a list of article categories on the page.',
      'category_slug' => 'Category slug',
      'category_slug_description' => "Look up the article category using the supplied slug value. This property is used by the default component partial for marking the currently active category.",
      'category_display_empty' => 'Display empty categories',
      'category_display_empty_description' => 'Show categories that do not have any articles.',
      'category_display_as_menu' => 'Display categories as a menu',
      'category_display_as_menu_description' => 'Display categories as a menu',
      'category_page' => 'Category page',
      'category_page_description' => 'Name of the category page file for the category links. This property is used by the default component partial.',
      'group_links' => 'Links',
      'article_title' => 'Article',
      'article_description' => 'Displays a article on the page.',
      'article_slug' => 'Article slug',
      'article_slug_description' => "Look up the article using the supplied slug value.",
      'article_category' => 'Category page',
      'article_category_description' => 'Name of the category page file for the category links. This property is used by the default component partial.',
      'articles_title' => 'Article List',
      'articles_description' => 'Displays a list of latest articles on the page.',
      'articles_pagination' => 'Page number',
      'articles_pagination_description' => 'This value is used to determine what page the user is on.',
      'articles_filter' => 'Category filter',
      'articles_filter_description' => 'Enter a category slug or URL parameter to filter the articles by. Leave empty to show all articles.',
      'articles_per_page' => 'Articles per page',
      'articles_per_page_validation' => 'Invalid format of the articles per page value',
      'articles_no_articles' => 'No articles message',
      'articles_no_articles_description' => 'Message to display in the article list in case if there are no articles. This property is used by the default component partial.',
      'articles_no_articles_default' => 'No articles found',
      'articles_order' => 'Article order',
      'articles_order_description' => 'Attribute on which the articles should be ordered',
      'articles_category' => 'Category page',
      'articles_category_description' => 'Name of the category page file for the "Posted into" category links. This property is used by the default component partial.',
      'articles_article' => 'Article page',
      'articles_article_description' => 'Name of the article page file for the "Learn more" links. This property is used by the default component partial.',
      'articles_except_article' => 'Except article',
      'articles_except_article_description' => 'Enter ID/URL or variable with article ID/URL you want to exclude. You may use a comma-separated list to specify multiple articles.',
      'articles_except_article_validation' => 'Article exceptions must be a single slug or ID, or a comma-separated list of slugs and IDs',
      'articles_except_categories' => 'Except categories',
      'articles_except_categories_description' => 'Enter a comma-separated list of category slugs or variable with such a list of categories you want to exclude',
      'articles_except_categories_validation' => 'Category exceptions must be a single category slug, or a comma-separated list of slugs',
      'group_exceptions' => 'Exceptions',
      'featured_title' => 'Featured',
      'featured_description' => 'Displays articles of a specific category in the home page.',
      'featured_id' => 'Category ID',
      'featured_id_description' => 'Enter the slug or the numeric id of a category to get the articles from. Add a "id:" prefix for numeric ids (eg: id:25).',
      'invalid_file_name' => 'Invalid file name. File name must start with: "category-level-" followed by a numeric value, (eg: category-level-1.htm). The numeric value refers to the depht of the category path.',
    ],
    'global_settings' => [
      'tab_general' => 'General',
      'max_characters' => 'Max characters',
      'max_characters_comment' => 'The maximum number of characters to display in category view',
      'show_breadcrumb_label' => 'Show breadcrumb',
      'show_breadcrumb_comment' => 'Show a breadcrumb in article and category views.',
    ],
    'messages' => [
      'required_field' => 'This field is required'
    ]
];
