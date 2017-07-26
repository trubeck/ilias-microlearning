<?php
/**
 * Created by IntelliJ IDEA.
 * User: trutz
 * Date: 10.07.17
 * Time: 14:52
 */

class ilNuggetRecommenderGUI
{
    function __construct()
    {
        global $tpl;

        $tpl->getStandardTemplate();

        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();
    }

    function executeCommand()
    {
        global $ilCtrl, $tpl;

        // determine next class in the call structure
        $next_class = $ilCtrl->getNextClass($this);

        switch($next_class)
        {
            // this would be the way to call a sub-GUI class
            /*                        case "ilbargui":
                                            $bar_gui = new ilBarGUI(...);
                                            $ret = $ilCtrl->forwardCommand($bar_gui);
                                            break;*/

            // process command, if current class is responsible to do so
            default:

                // determin the current command (take "view" as default)
                $cmd = $ilCtrl->getCmd("view");
                if (in_array($cmd, array("view")))
                {
                    $this->$cmd();
                }
                break;
        }

        $tpl->show();
    }

    function view()
    {
        global $tpl;

        $my_tpl = new ilTemplate('beautify.html', true, true,'Services/NuggetRecommender');


        include_once "./Services/NuggetRecommender/classes/class.ilNuggetRecommender.php";

        $recommender = new ilNuggetRecommender();

        $recommendedTitles = $recommender->recommend();

        $display ="";

        for($x = 0; $x<count($recommendedTitles); $x++)
        {
            $display .= "<div>" . $recommendedTitles[$x] . "</div>";
        }

        $my_tpl->setVariable("RECOM", $display);

        $tpl->setContent($my_tpl->get());



    }





}