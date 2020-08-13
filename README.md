# Total Cookie Consent plugin for Craft 3

The plugin provides total control over the cookie consent collection process and includes three consent collection options: No Consent, Implied Consent, and Explicit Consent. Collection methods can be tailored per country or state to provide an optimal non-intrusive user experience.

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require pageworks/total-cookie-consent

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Total Cookie Consent.

## Usage

Add the following twig to your project base template to begin using the plugin.

```twig
{% hook 'total-cookie-consent' %}

{% set consent = craft.tcc.consent() %}
{% if consent['statistics'] %}
        {# statistics code #}
{% endif %}
{% if consent['marketing'] %}
        {# marketing code #}
{% endif %}
{% if consent['necessary'] %}
        {# necessary code #}
{% endif %}
```