<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Welcome to CodeIgniter</title>

        <style type="text/css">

            ::selection{ background-color: #E13300; color: white; }
            ::moz-selection{ background-color: #E13300; color: white; }
            ::webkit-selection{ background-color: #E13300; color: white; }

            body {
                background-color: #fff;
                margin: 40px;
                font: 13px/20px normal Helvetica, Arial, sans-serif;
                color: #4F5155;
            }

            a {
                color: #003399;
                background-color: transparent;
                font-weight: normal;
            }

            h1 {
                color: #444;
                background-color: transparent;
                border-bottom: 1px solid #D0D0D0;
                font-size: 19px;
                font-weight: normal;
                margin: 0 0 14px 0;
                padding: 14px 15px 10px 15px;
            }

            .xml_result {

            }

            #body{
                margin: 0 15px 0 15px;
            }

            p.footer{
                text-align: right;
                font-size: 11px;
                border-top: 1px solid #D0D0D0;
                line-height: 32px;
                padding: 0 10px 0 10px;
                margin: 20px 0 0 0;
            }

            #container{
                margin: 10px;
                border: 1px solid #D0D0D0;
                -webkit-box-shadow: 0 0 8px #D0D0D0;
            }
        </style>
        <link type="text/css" rel="stylesheet" href="<?php echo base_url("SyntaxHighlighter/Styles/SyntaxHighlighter.css"); ?>"></link>
        <script language="javascript" src="<?php echo base_url("SyntaxHighlighter/Scripts/shCore.js"); ?>"></script>
        <script language="javascript" src="<?php echo base_url("SyntaxHighlighter/Scripts/shBrushXml.js"); ?>"></script>
        <script language="javascript">
            dp.SyntaxHighlighter.ClipboardSwf = '<?php echo base_url("SyntaxHighlighter/Scripts/clipboard.swf"); ?>';
            dp.SyntaxHighlighter.HighlightAll('code');
        </script>
        <link href="<?php echo base_url("google-code-prettify/prettify.css"); ?>" type="text/css" rel="stylesheet" />
        <script type="text/javascript" src="<?php echo base_url("google-code-prettify/prettify.js"); ?>"></script>
    </head>
    <body onload="prettyPrint()">

        <div id="container">
            <h1>Welcome to CodeIgniter!</h1>

            <div id="body">
                <h2>Message</h2>
                <pre class="prettyprint"><code class="language-xml"><?php echo htmlentities($message); ?></code></pre>

                <h2>Result</h2>
                <pre class="prettyprint"><code class="language-xml"><?php echo htmlentities($result); ?></code></pre>

                <h2>Error</h2>
                <pre><?php echo $error; ?></pre>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>

    </body>
</html>