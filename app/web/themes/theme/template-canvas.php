<?php

/**
 * Template Name: Canvas
 */

use Timber\Timber;
$context = Timber::context();
Timber::render('@theme/templates/canvas.twig', $context);