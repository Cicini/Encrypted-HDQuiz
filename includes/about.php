<?php

if (!current_user_can('edit_others_pages')) {
    die("Your user account does not have access to these settings");
}


wp_enqueue_style(
    'hdq_admin_style',
    plugin_dir_url(__FILE__) . 'css/hdq_admin.css?v=' . HDQ_PLUGIN_VERSION
);

wp_enqueue_script(
    'hdq_admin_script',
    plugins_url('/js/hdq_admin.js?v=' . HDQ_PLUGIN_VERSION, __FILE__),
    array('jquery', 'jquery-ui-draggable'),
    HDQ_PLUGIN_VERSION,
    true
);
?>
<div id="main" style="max-width: 900px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
    <div id="header">
        <h1 id="heading_title" style="margin-top:0">
            HD Quiz - About / Options
        </h1>
    </div>


    <p>HD Quiz was designed and developed to be one of the easiest and most hassle free quiz builders for WordPress. If
        you
        have any questions, or need support, please contact me at the <a href="https://wordpress.org/support/plugin/hd-quiz" target="_blank">official WordPress HD Quiz support
            forum</a>.</p>

    <p>As I continue to develop HD Quiz, more features, options, customizations, and settings will be introduced. If you
        have enjoyed HD Quiz, then I would sure appreciate it if you could <a href="https://wordpress.org/support/plugin/hd-quiz/reviews/#new-post" target="_blank">leave an honest
            review</a>. It's the little things that make building systems like this worthwhile ❤.</p>

    <hr style="margin-top:2rem" />


    <?php wp_nonce_field('hdq_about_options_nonce', 'hdq_about_options_nonce'); ?>

    <div style="display: grid; grid-template-columns: 1fr max-content; align-items: center;">
        <h2>
            Settings
        </h2>
        <div>
            <div role="button" title="save HDQ settings" class="hdq_button" id="hdq_save_settings">SAVE</div>
        </div>
    </div>


    <?php
    $fields = hdq_get_settings();
    if (!isset($quizID)) {
        $quizID = "";
    }
    ?>

    <div id="hdq_settings_page" class="content" style="display: block">
        <div id="content_tabs">
            <div id="tab_nav_wrapper">
                <div id="hdq_logo">
                    <span class="hdq_logo_tooltip"><img src="<?php echo plugins_url('/images/hd-logo.png', __FILE__); ?>" alt="Harmonic Design logo">
                        <span class="hdq_logo_tooltip_content">
                            <span><strong>HD Quiz</strong> is developed by Harmonic Design. Check out the addons page to see how you can extend HD Quiz even further.</span>
                        </span>
                    </span>
                </div>
                <div id="tab_nav">
                    <?php hdq_print_settings_tabs(); ?>
                </div>
            </div>
            <div id="tab_content">
                <input type="hidden" class="hderp_input" id="quiz_id" style="display:none" data-required="true" data-type="integer" value="<?php echo $quizID; ?>" />
                <?php hdq_print_settings_tab_content($fields); ?>
            </div>
        </div>
    </div>



    <div class="hdq_highlight" id="hd_patreon">
        <div id="hd_patreon_icon">
            <img src="<?php echo plugins_url('/images/hd_patreon.png', __FILE__); ?>" alt="Donate">
        </div>
        <p>
            HD Quiz is a 100% free plugin developed in my spare time, and as such, I get paid in nothing but good will
            and positive reviews. If you are enjoying HD Quiz and would like to show your support, please consider
            contributing to my <a href="https://www.patreon.com/harmonic_design" target="_blank">patreon page</a> to
            help continued development. Every little bit helps, and I am fuelled by ☕.
        </p>
    </div>
    <br />

    <h2>Upcoming Features</h2>
    <p>I am developing HD Quiz in my spare time, but still plan to add the following features at some point</p>
    <ul class="hdq_list">
        <li>Quiz styler <span class="hdq_tooltip hdq_tooltip_question">?<span class="hdq_tooltip_content"><span>This
                        would allow you to change the fonts and colours of the quizzes across your
                        site</span></span></span></li>
        <li>Logged in only <span class="hdq_tooltip hdq_tooltip_question">?<span class="hdq_tooltip_content"><span>This
                        would hide the quiz for non-registered users.</span></span></span></li>
        <li>Global / default quiz options <span class="hdq_tooltip hdq_tooltip_question">?<span class="hdq_tooltip_content"><span>These would become the default for all quizzes, but could be
                        overridden on a per-quiz basis.</span></span></span></li>
        <li>Translation ready (please contacting me if you are interested in helping with translations)</li>
    </ul>

    <h2>Quick Documentation</h2>
    <center>
        <iframe width="560" height="315" src="https://www.youtube.com/embed/IgDada_WqNw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        <p>
            <small>watch a full tutorial on how to use HD Quiz</small>
        </p>

    </center>
    <br />

    <p>HD Quiz was designed to be as easy and intuitive to use as possible. However, I understand that some guidance
        might still be needed. The following are the "quick steps" needed to create your first quiz!</p>

    <div class="hdq_accordion">
        <h3>Using A Quiz - Adding Quiz To a Page</h3>
        <div>
            <p>
                HD Quiz uses shortcodes to render a quiz, so you can place a quiz almost anywhere on your site!
            </p>
            <ul class="hdq_list">
                <li>To find the shortcode for a quiz, select HD Quiz -&gt; Quizzes in the left menu.</li>
                <li>You will now see a list of all of your quizzes in a table, with the shortcode listed. <span class="hdq_tooltip hdq_tooltip_question">?<span class="hdq_tooltip_content"><span>Example shortcode <code>[HDquiz quiz = "xxx"]</code></span></span></span></li>
                <li>Copy and paste the shortcode into any page or post you want to render that quiz!</li>
            </ul>
            <p>
                <strong>Gutenberg</strong>: HD Quiz is also fully Gutenberg compatible, and I even coded in a custom
                Gutenberg block to make adding quizzes easier than ever! If you are using Gutenberg, then you can add
                the HD Quiz block. A block will be added to your editor and will automatically populate a list of all of
                your quizzes. Simply select the quiz you wish to add and save.
            </p>
        </div>
    </div>
    <div class="hdq_accordion">
        <h3>Changing Question Order</h3>
        <div>
            <p>
                The latest and greatest version of HD Quiz makes creating custom question order easier than
                ever! When
                editing a quiz, you can simply drag and drop to change the order of the questions. Just
                remember to save
                the quiz when done!
            </p>
        </div>
    </div>
    <div class="hdq_accordion">
        <h3>Need More Help?</h3>
        <div>
            <p>
                This is a free WordPress plugin, so I just get pure unfiltered satisfaction knowing that you use
                and love HD Quiz.</p>
            <p>So, loyal HD Quiz user, if you need help, please don't hesitate to leave us a message or question on the
                <a href="https://wordpress.org/support/plugin/hd-quiz" target="_blank">official WordPress HD Quiz
                    Support Forum</a>, or on our own <a href="http://harmonicdesign.ca/hd-quiz/" target="_blank">support
                    page at Harmonic Design</a>.
            </p>
        </div>
    </div>
</div>