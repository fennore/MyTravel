
{% extends "default.tpl" %}

{% block title %}Location list{% endblock %}

{% block content %}
<main id="content">
  <dl>
    {% for location in locationlist %}
    <dt>{{location.info}}</dt>
    <dd>{{location}}</dd>
    {% endfor %}
  </dl>
</main>
<nav>
  {% for stage in stages %}
    <a href="{{path('locations', {stage:stage})}}">stage {{stage}}</a> 
  {% endfor %}
</nav>
{% endblock %}