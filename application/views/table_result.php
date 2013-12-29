<ul class="nav nav-tabs">
    <li class="active">
        <a href="#">Sonuçlar</a>
    </li>
    <li>
        <a href="#">+/- 3 Gün</a>
    </li>
    <li>
        <a href="#">Uçuşları Düzenle</a>
    </li>
</ul>

<?php
$prices = array();
$price_contents = array();


foreach ($results as $key => $result) {
    $prices[] = $result->TotalPrice;
    @$price_contents["$result->TotalPrice"][] = $result->$key;
}


$prices = array_unique($prices);
?>


<div class="accordion" id="accordion2">
    <?php
    foreach ($prices as $key_price => $price) {
        echo '    <div class="accordion-group">';
        echo '        <div class="accordion-heading">';
        echo '            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse' . $key_price . '">Sadece <b>' . $price . '</b> !</a>';
        echo '        </div>';
        echo '        <div id="collapse' . $key_price . '" class="accordion-body collapse">';
        echo '            <div class="accordion-inner">';
//        $gidis_kalkis = array();
//        $gidis_inis = array();
//        $donus_kalkis = array();
//        $donus_inis = array();
        $gidis = array();
        $donus = array();
        foreach ($results as $key => $value) {
            if ("$value->TotalPrice" == "$price") {
                $num = count($segments[$key]);
                $gidis[$key] = array();
                $donus[$key] = array();

                $gidiste_miyiz = false;
                $donuste_miyiz = false;

                foreach ($segments[$key] as $segment) {

                    if ($gidiste_miyiz) {
                        $gidis[$key][] = "$segment";
                        if ("{$flights["$segment"]->Destination}" == $this->input->post("to")) {
                            $gidiste_miyiz = false;
                        }
                    } else if ("{$flights["$segment"]->Origin}" == $this->input->post("from") && $gidiste_miyiz == false) {
                        $gidiste_miyiz = true;
                        $gidis[$key][] = "$segment";
                        if ("{$flights["$segment"]->Destination}" == $this->input->post("to")) {
                            $gidiste_miyiz = false;
                        }
                    } else if ($donuste_miyiz) {
                        $donus[$key][] = "$segment";
                        if ("{$flights["$segment"]->Destination}" == $this->input->post("from")) {
                            $donuste_miyiz = false;
                        }
                    } else if ("{$flights["$segment"]->Origin}" == $this->input->post("to") && $donuste_miyiz == false) {
                        $donuste_miyiz = true;
                        $donus[$key][] = "$segment";
                        if ("{$flights["$segment"]->Destination}" == $this->input->post("from")) {
                            $donuste_miyiz = false;
                        }
                    }
                }
//                $td = '';
//
//                if ($num > 1)
//                    $td = 'rowspan="' . $num . '"';
//                echo '<tr>';
//                echo '<td ' . $td . '><input type="radio"></td>';
//                echo '<td ' . $td . '>' . $value->TotalPrice . '</td>';
//
//                if ($num == 1)
//                    echo "<td " . $td . ">Direkt</td>";
//                else
//                    echo "<td " . $td . ">$num</td>";
//
//                if ($num > 1) {
//                    foreach ($segments[$key] as $segment) {
//                        $temp = $flights["$segment"];
//                        echo "<td>{$flights["$segment"]->Carrier}</td>";
//                        echo "<td>{$flights["$segment"]->FlightNumber}</td>";
//                        $kalkis = date("j.n.Y H:i", strtotime($flights["$segment"]->DepartureTime));
//                        $bitis = date("j.n.Y H:i", strtotime($flights["$segment"]->ArrivalTime));
//                        echo "<td>{$flights["$segment"]->Origin} $kalkis</td>";
//                        echo "<td>{$flights["$segment"]->Destination} $bitis</td>";
//
//                        echo "</tr><tr>";
//                    }
//                } else {
//                    foreach ($segments[$key] as $segment) {
//                        echo "<td>{$flights["$segment"]->Carrier}</td>";
//                        echo "<td>{$flights["$segment"]->FlightNumber}</td>";
//                        $kalkis = date("j.n.Y H:i", strtotime($flights["$segment"]->DepartureTime));
//                        $bitis = date("j.n.Y H:i", strtotime($flights["$segment"]->ArrivalTime));
//                        echo "<td>{$flights["$segment"]->Origin} $kalkis</td>";
//                        echo "<td>{$flights["$segment"]->Destination} $bitis</td>";
//                    }
//                }
//
//
//
//                echo "</tr>";
            }
        }
//        $donus_inis = array_unique($donus_inis);
//        $gidis_kalkis = array_unique($gidis_kalkis);
//        $donus_kalkis = array_unique($donus_kalkis);
//        $gidis_inis = array_unique($gidis_inis);
        ?>
        <h5>Gidiş</h5>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <td width="10">#</td>
                    <td width="30">Aktarma Sayısı</td>
                    <td width="40">Kalkış</td>
                    <td width="40">Varış</td>
                    <td width="10">Havayolu</td>
                    <td width="30">Uçuş No</td>
                </tr>
            </thead>
            <tbody>
    <?php
    $gidisler = array();
    foreach ($gidis as $key => $segment1) {
//        $temp = "";
//        $temp .= '<tr><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td></tr>';
        
        $temp_gidis = "";
        foreach ($segment1 as $segment) {
            $temp_gidis .= $segment;
        }
        
        if (!in_array(md5($temp_gidis), $gidisler)) {
            //foreach ($segment1 as $segment) {

                $temp = "";
                $temp .= '<tr>';
                $temp .= '<td><input type="radio"></td>';
                $aktarma = ((count($segment1) - 1) == 0) ? "Direkt" : (count($segment1) - 1);
                $temp .= '<td><a href="#" class="btn" rel="popover"data-content="Uçuş Bilgileri Yer alacak...">' . $aktarma ."</a></td>";
                $kalkis = date("j.n.Y H:i", strtotime($flights[array_shift(array_values($segment1))]->DepartureTime));
                $bitis = date("j.n.Y H:i", strtotime($flights[end($segment1)]->ArrivalTime));
                $temp .= "<td>{$flights[array_shift(array_values($segment1))]->Origin} $kalkis</td>";
                $temp .= "<td>{$flights[end($segment1)]->Destination} $bitis</td>";
                $temp .= "<td>{$flights["$segment"]->Carrier}</td>";
                $temp .= "<td>{$flights["$segment"]->FlightNumber}</td>";
                $temp .= '</tr>';

    //        if (!in_array(md5($temp), $gidis)) {
    //            echo $temp;
    //            $gidis[] = md5($temp);
    //        }
                echo $temp;
            //}
            $gidisler[] = md5($temp_gidis);
        }
    }
    ?>
            </tbody>
        </table>

        <h5>Dönüş</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td width="10">#</td>
                    <td width="30">Aktarma Sayısı</td>
                    <td width="40">Kalkış</td>
                    <td width="40">Varış</td>
                    <td width="10">Havayolu</td>
                    <td width="30">Uçuş No</td>
                </tr>
            </thead>
            <tbody>
    <?php
    $donusler = array();
    foreach ($donus as $key => $segment1) {
//        $temp = "";
//        $temp .= '<tr><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td></tr>';
        
        $temp_donus = "";
        foreach ($segment1 as $segment) {
            $temp_donus .= $segment;
        }
        
        if (!in_array(md5($temp_donus), $donusler)) {
            //foreach ($segment1 as $segment) {

                $temp = "";
                $temp .= '<tr>';
                $temp .= '<td><input type="radio"></td>';
                $aktarma = ((count($segment1) - 1) == 0) ? "Direkt" : (count($segment1) - 1);
                $temp .= '<td><a href="#" class="btn" rel="popover"data-content="Uçuş Bilgileri Yer alacak...">' . $aktarma ."</a></td>";
                $kalkis = date("j.n.Y H:i", strtotime($flights[array_shift(array_values($segment1))]->DepartureTime));
                $bitis = date("j.n.Y H:i", strtotime($flights[end($segment1)]->ArrivalTime));
                $temp .= "<td>{$flights[array_shift(array_values($segment1))]->Origin} $kalkis</td>";
                $temp .= "<td>{$flights[end($segment1)]->Destination} $bitis</td>";
                $temp .= "<td>{$flights["$segment"]->Carrier}</td>";
                $temp .= "<td>{$flights["$segment"]->FlightNumber}</td>";
                $temp .= '</tr>';

    //        if (!in_array(md5($temp), $gidis)) {
    //            echo $temp;
    //            $gidis[] = md5($temp);
    //        }
                echo $temp;
            //}
            $donusler[] = md5($temp_donus);
        }
    }
    ?>
            </tbody>
        </table>

                <?php
                echo '            </div>';
                echo '        </div>';
                echo '    </div>';
            }
            ?>
</div>
