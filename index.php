<?php
/**
 * Plugin Name:       OCC Auto Live Switcher
 * Description:       The plugin offers the essential feature of automatically switching text between "live" and "watch" based on a predetermined time of day when the sermon's video stream is live. Example usage: [auto_live_switch day="sunday" start_hour="10" start_minute="00" end_hour="12" end_minute="00" /]
 * Version:           0.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            David Roth
 * Author URI:        https://efficientitpros.com 
 *
 *
 * @author David Roth, david@efficientitpros.com
 *
 */
namespace occ {
    class LiveSwitch
    {
        private $style = '
            <style>
                .live-overline {
                    font-family: Arial;
                    font-weight: bold;
                    font-weight: 700;
                    font-size: 3em;
                    color: #eabec4!important;
                    text-align: center;
                }
                .live-overline.is-pulsing {
                    position: relative;
                    margin-left: 16px;
                }
                .live-overline.is-pulsing:before {
                    content: "";
                    display: block;
                    position: absolute;
                    left: 61px;
                    top: 10px;
                    background-color: #ea5841;
                    border-radius: 50%;
                    height: 30px;
                    width: 30px;
                    opacity: 0;
                    animation: pulse 1.25s linear;
                    animation-iteration-count: infinite;
                }
                .live-overline.is-pulsing:after {
                    content: "";
                    display: block;
                    position: absolute;
                    left: 69px;
                    top: 18px;
                    background-color: #ea5841;
                    border-radius: 50%;
                    height: 14px;
                    width: 14px;
                }
                @-webkit-keyframes pulse {
                    0% {
                        transform: scale(0.25);
                        opacity: 0.5;
                    }
                    50% {
                        opacity: 0.8;
                    }
                    100% {
                        transform: scale(1);
                        opacity: 0;
                    }
                }
            </style>
        ';

        function __construct()
        {
            add_shortcode('auto_live_switch', array($this, 'setVideoText'));
        }

        function setVideoText($atts, $content)
        {
            $show_record = "";
            $output_text = "[Latest Sermon]";
            $output_content = '
                <div>
                    <h2 class="live-overline ' . $show_record . '">' . $output_text . '</h2>
                </div>';

            if (
                !empty($atts) && 
                array_key_exists("day", $atts) && 
                array_key_exists("start_hour", $atts) && 
                array_key_exists("end_hour", $atts) && 
                array_key_exists("start_minute", $atts) && 
                array_key_exists("end_minute", $atts)) 
            {
                $day = $atts['day'];
                $start_hour = $atts['start_hour'];
                $start_minute = $atts['start_minute'];
                $end_hour = $atts['end_hour'];
                $end_minute = $atts['end_minute'];

                $timezone = new \DateTimeZone('America/Chicago');
                
                # Get start time
                $start_dt = new \DateTime();
                $start_dt->setTimezone($timezone);
                $start_dt->setTime($start_hour, $start_minute);

                # Get start time
                $end_dt = new \DateTime();
                $end_dt->setTimezone($timezone);
                $end_dt->setTime($end_hour, $end_minute);

                # Get current time
                $current_dt = new \DateTime();
                $current_dt->setTimezone($timezone);

                # Get day of the week
                $day_of_week = $current_dt->format("l");

                error_log(print_r("[*] auto_live_switch: ON [" . $day_of_week . "] BETWEEN [" . $start_dt->format(\DateTime::ISO8601) . "] AND [" . $end_dt->format(\DateTime::ISO8601) . "] GO LIVE!", true));

                // Compare current time to start_dt to determine if "Live"
                if (
                    strtolower($day_of_week) == strtolower($day) 
                    && $start_dt <= $current_dt && 
                    $end_dt >= $current_dt) 
                {
                    $show_record = "is-pulsing";
                    $output_text = "Watch Live";
                } else {
                    $show_record = "";
                    $output_text = "Latest Sermon";
                }

                // If content, then show content
                if (!empty($content)) {
                    $output_content .= $content;
                } else {
                    $output_content = '
                    <div>
                        <h2 class="live-overline ' . $show_record . '">' . $output_text . '</h2>
                    </div>';
                }
            }

            return $this->style . "\n" . $output_content;
        }
    }

    new LiveSwitch;
}