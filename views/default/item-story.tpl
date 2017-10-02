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
    {{item.content|striptags('<br>')|raw}}
  </article>
</main>
<nav>
  {% for listitem in itemList %}
  <a href="{{path('story', {title:listitem.path})}}">{{listitem.title}}</a> 
  {% endfor %}
</nav>
{% endblock %}
