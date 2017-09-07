{% extends "default.tpl" %}

{% block title %}{{item.title}}{% endblock %}

{% block content %}
<div id="content">
  <article>
    <header>
      <h1>
        {{item.title}}
      </h1>
    </header>
    {{item.content}}
  </article>
  <aside>
    {% for item in itemList %}
    <a href="{{path('item', {title:item.path})}}">{{item.title}}</a><br>
    {% endfor %}
  </aside>
</div>
{% endblock %}