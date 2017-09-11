{% extends "default.tpl" %}

{% block title %}403 Time to turn around{% endblock %}

{% block head %}
    {{ parent() }}
{% endblock %}

{% block content %}
<main id="content">
  <article>
    <header>
      <h1>Not allowed 403</h1>
    </header>
    <p>
      You forgot your paper work during your travels.<br>
      No crossing the border this way!
    </p>
  </article>
</main>
{% endblock %}