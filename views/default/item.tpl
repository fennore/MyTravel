{% extends "default.tpl" %}

{% block title %}{{item.title}}{% endblock %}

{% block content %}
<main id="content">
  <article>
    <header>
      <h1>
        {{item.title}}
      </h1>
    </header>
    {{item.content}}
  </article>
</main>
<nav>
  {% for item in itemList %}
  <a href="{{path('item', {title:item.path})}}">{{item.title}}</a> 
  {% endfor %}
</nav>
{% endblock %}