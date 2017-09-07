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
    <figure>
      <img src="{{basepath}}/{{item.imagepath}}" />
      {{item.content}}
    </figure>
  </article>
  <aside>
    {% for item in itemList %}
    <a href="{{basepath}}/{{item.path}}"><img src="{{basepath}}/{{item.imagepath}}/thumbnail" /> {{item.title}}</a><br>
    {% endfor %}
  </aside>
</div>
{% endblock %}