<?php
$trans = include "folder/name.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>چقدر ارز دارم</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<div class="text-center my-header">
    <h1>محاسبه تومانی و دلاری ارز</h1>
</div>
<div class="container">
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <label for="" class="float-right">چی داری</label>
                <select name="" id="arz" class="form-control">
                    <option value="">Select</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="" class="float-right">چندتا داری</label>
                <input type="number" class="form-control ltr" id="chandta">
            </div>
        </div>
    </div>
    <h2 class="text-center mt-4">لیست</h2>
    <div class="row">
        <div class="col-md-3">
            <input type="text" id="myID" placeholder="شناسه یونیک خودت رو وارد کن" class="form-control">
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-3 flex-row d-flex align-items-end">
            <label for="">دلار</label>
            <input type="number" value="26000" id="dollar" placeholder="قیمت دلار" class="form-control ltr">
        </div>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="text-right">نام</th>
            <th>مقدار</th>
            <th>مقدار به دلار</th>
            <th>مقدار به تومان</th>
        </tr>
        </thead>
        <tbody id="mytbody">

        </tbody>
        <tfoot>
        <tr data-name="total">
            <td>جمع کل</td>
            <td></td>
            <td id="total_usd"></td>
            <td id="total_toman"></td>
        </tr>
        </tfoot>
    </table>
</div>
<div class="text-center">
    <div id="save" class="btn btn-success">ذخیره ش کن برام که دفعه بعد که اومدم باشه</div>
</div>
</body>
</html>
<script>
    $(function () {
        let rates = '';
        let trans = <?php echo json_encode($trans); ?>;
        let url      = window.location.href;
        let dollar = $('#dollar').val();
        $.ajax({
            url: 'http://api.coinlayer.com/api/live?access_key=b718b767e1946440d45eddf7f5edd0ae',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                rates = response.rates;
                let html = "<option value=''>انتخاب کن</option>";
                for(let item in trans)
                {
                    if (rates[item]){
                        html += "<option value='" + item + "'><span>" + trans[item] + ' (' + item + ')' + "</span></option>";
                    }
                }
                $('#arz').html(html);

                let name = getUrlParameter('name');
                if (name)
                {
                    getUnique(name);
                }
            }
        });
        // $('select').select2();
        let $select = $('select');

        $select.select2({
            templateResult: updateSelectTemplate,
            templateSelection: updateSelectTemplate
        })

        function updateSelectTemplate(state) {
            if (!state.id) {
                return state.text;
            }
            let $state = $(
                '<span>' + state.text + '</span><span>' + (addCommas(Math.floor(rates[state.id] * dollar)) + ' تومان') + '</span>'
            );
            return $state;
        }

        $('select').on('click', function(e) {
            $select.find('option').data('display', 'Some new value');
            $select.trigger('change');
        });
        let chi = "";
        $('#arz').on('change', function () {
            dollar = $('#dollar').val();
            let value = $(this).val();
            let myText = $("#arz option:selected").text();
            $('#chandta').val(changeCheqadr(value));
            chi = value;
            let tbody = $('#mytbody');
            let tr = $('tbody > tr[data-name=' + value + ']');
            if (tr.length) {

            } else {
                tbody.append("<tr data-name='" + value + "'><td class='text-right'>" + myText + "</td><td class='arz ltr'>" + 0 + "</td><td class='usd ltr'>" + 0 + "</td><td class='toman ltr'>" + 0 + "</td></tr>");
            }
            $('#chandta').focus();
        });

        let yourArz = {};
        $('#chandta').on('keyup paste', function () {
            dollar = $('#dollar').val();
            let thisval = $(this).val();
            let thistr = $('tr[data-name=' + chi + ']');
            // if (thisval == 0) thistr.remove();
            thistr.children('td.arz').text(thisval);
            thistr.children('td.usd').text(thisval * rates[chi]);
            thistr.children('td.toman').text(addCommas(Math.floor(thisval * rates[chi] * dollar)));
            yourArz[chi] = thisval;

            create_total();
        });

        function changeCheqadr(val) {
            return $('body tbody > tr[data-name=' + val + ']').children('td.arz').text();
        }

        $('#save').on('click',function (){
            let myId = $('#myID').val();
            if (!myId)
            {
                alert('اول شناسه یونیک خودت رو وارد کن');
                $('#myID').focus();
            }else{
                $.ajax({
                    url: 'tt.php',
                    type: 'POST',
                    data: {
                        data: yourArz,
                        name: myId
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                    }
                })
            }
        });

        $('#myID').on('blur',function (){
            getUnique($(this).val());
        });

        function buildTable(res){
            dollar = $('#dollar').val();
            let html = "";
            for (let item in res) {
                html += "<tr data-name='" + item + "'><td class='text-right'>" + trans[item] + ' (' + item + ')' + "</td><td class='arz ltr'>" + res[item] + "</td><td class='usd ltr'>" + res[item] * rates[item] + "</td><td class='toman ltr'>" + addCommas(Math.floor(res[item] * rates[item] * dollar)) + "</td></tr>";
            }
            yourArz = res;
            $('tbody').html(html);
            create_total();
        }

        function create_total() {
            let totalUsd = 0;
            let totalToman = 0;
            for (let item in yourArz) {
                totalUsd += yourArz[item] * rates[item];
                totalToman += yourArz[item] * rates[item] * dollar;
            }
            $('#total_usd').text(addCommas(totalUsd));
            $('#total_toman').text(addCommas(Math.floor(totalToman)));
        }

        function addCommas(nStr)
        {
            nStr += "";
            let persinaDigits1 = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            for (let i =0;i<10;i++)
            {
                nStr = nStr.replace(persinaDigits1[i],i);
            }
            nStr += '';
            let x = nStr.split('.');
            let x1 = x[0];
            let x2 = x.length > 1 ? '.' + x[1] : '';
            let rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            return x1 + x2;
        }

        $('span.select2-selection.select2-selection--single').addClass('form-control');
        let getUrlParameter = function getUrlParameter(sParam) {
            let sPageURL = window.location.search.substring(1),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                }
            }
            return false;
        };

        function getUnique(name) {
            dollar = $('#dollar').val();
           setTimeout(function () {
               $.ajax({
                   url: 'll.php',
                   type: 'POST',
                   data: {
                       data: name
                   },
                   dataType: 'json',
                   success: function (response) {
                       if (response)
                       {
                           buildTable(response);
                       }
                   }
               });
               window.history.replaceState(null, null, "?name=" + name);

           },1000);
        }

    })
</script>
<?php

//$curl = curl_init();
//
//curl_setopt_array($curl, array(
//    CURLOPT_URL => "http://api.coinlayer.com/api/live?access_key=b718b767e1946440d45eddf7f5edd0ae&symbols=BTC%2CETH%2CBCH%2CNEO%2CBAT%2CLINK%2CDOGE",
//    CURLOPT_RETURNTRANSFER => true,
//    CURLOPT_ENCODING => "",
//    CURLOPT_MAXREDIRS => 10,
//    CURLOPT_TIMEOUT => 30,
//    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//    CURLOPT_CUSTOMREQUEST => "GET",
//    CURLOPT_POSTFIELDS => "{\n\t\"subscriber_number\" : \"123456789\",\n\t\"nick_name\" : \"home\"\n}",
//    CURLOPT_HTTPHEADER => array(
//        "cache-control: no-cache",
//        "content-type: application/json",
//        "postman-token: d4fd85af-5de8-3837-9bb8-d74a2a61e307",
//        "token: 50d399df7afcbff296f1a1b104d4291a1071085318"
//    ),
//));
//
//$response = curl_exec($curl);
//$err = curl_error($curl);
//
//curl_close($curl);
//
//if ($err) {
//    echo "cURL Error #:" . $err;
//} else {
//    if ($response) {
//        var_dump($response);
//        $response = json_decode($response, true);
//        $target = $response['rates'];
//        $dollar = dollar;
//        $usd_btc = floatval($target['BTC']) * .006999;
//        $rial_btc = $usd_btc * $dollar;
//        $usd_eth = floatval($target['ETH']) * .372333;
//        $rial_eth = $usd_eth * $dollar;
//        $usd_bat = floatval($target['BAT']) * 346.22;
//        $rial_bat = $usd_bat * $dollar;
//        $usd_neo = floatval($target['NEO']) * 3.89;
//        $rial_neo = $usd_neo * $dollar;
//        $usd_link = floatval($target['LINK']) * 10;
//        $rial_link = $usd_link * $dollar;
//        $usd_bch = floatval($target['BCH']) * .2;
//        $rial_bch = $usd_bch * $dollar;
//        $usd_doge = floatval($target['DOGE']) * 2220;
//        $rial_doge = $usd_doge * $dollar;
//        $total_usd = $usd_btc + $usd_eth + $usd_bat + $usd_neo + $usd_link + $usd_bch + $usd_doge;
//        $total_rial = $rial_btc + $rial_eth + $rial_bat + $rial_neo + $rial_link + $rial_bch + $rial_doge;
//        ?>
<!--        <style>-->
<!--            table, td {-->
<!--                border: 1px solid black;-->
<!--            }-->
<!--        </style>-->
<!--        <table>-->
<!--            <tr>-->
<!--                <td>BTC</td>-->
<!--                <td>--><?//= $usd_btc ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_btc) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>ETH</td>-->
<!--                <td>--><?//= $usd_eth ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_eth) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>LINK</td>-->
<!--                <td>--><?//= $usd_link ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_link) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>NEO</td>-->
<!--                <td>--><?//= $usd_neo ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_neo) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>BCH</td>-->
<!--                <td>--><?//= $usd_bch ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_bch) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>BAT</td>-->
<!--                <td>--><?//= $usd_bat ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_bat) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>DOGE</td>-->
<!--                <td>--><?//= $usd_doge ?><!--</td>-->
<!--                <td>--><?//= number_format($rial_doge) ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>TOTAL</td>-->
<!--                <td>--><?//= number_format($total_usd) ?><!--</td>-->
<!--                <td>--><?//= number_format($total_rial) ?><!--</td>-->
<!--            </tr>-->
<!--        </table>-->
<!---->
<!--        --><?php
//    }
//}
