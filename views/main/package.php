<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

$this->title                   = Yii::t('app', 'Package information');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('app', 'Main page'),
    'url'   => Url::to(['main/index'])
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Package information');

?>
<div class="site-package">
    <h1><?php echo $this->title; ?></h1>

    <ul>
        <li><a href="#cdek">Требования к упаковке отправлений СДЕК</a></li>
        <li><a href="#boxberry">Требования к упаковке отправлений Boxberry</a></li>
        <li><a href="#russian-post">Требования к упаковке почтовых отправлений</a></li>
        <li><a href="#iml">Требования к упаковке отправлений IML</a></li>
    </ul>

    <h3 id="cdek">Требования к упаковке отправлений СДЕК</h3>
    <p></p>
    <p></p>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td width="312">
                <p><strong>Виды вложений</strong></p>
            </td>
            <td width="332">
                <p><strong>Тип упаковки</strong></p>
            </td>
        </tr>
        <tr>
            <td width="312">
                <p>Документы</p>
            </td>
            <td width="332">
                <p>Картонные конверты СДЭК/коробки</p>
            </td>
        </tr>
        <tr>
            <td width="312">
                <p>&laquo;Мелкая&raquo; бытовая техника до 2 кг (телефоны, планшеты, ноутбуки, утюги, фены и так
                    далее.), полиграфия (книги, буклеты, печатная продукция)</p>
            </td>
            <td width="332">
                <p>Коробки из гофрокартона с амортизационными прокладками из пенопласта, крафтпакетов, пупырчатой
                    пленки, либо другой наполнитель обеспечивающим сохранность ТМЦ ,по внутреннему периметру
                    коробки и между вложениями, если единиц товара более одной в коробке.</p>
            </td>
        </tr>
        <tr>
            <td width="312">
                <p>&laquo;Средняя и крупная&raquo; бытовая техника, сантехника (раковины, унитазы, ванны, душевые
                    кабины), керамическая плитка, посуда(стеклянная, фарфоровая, металлическая), мебель, строительные и
                    отделочные материалы, спортивное, производственное, медицинское, промышленное оборудование, изделия
                    из хрупких материалов (стекло, керамика, фарфор, хрусталь и тд)</p>
                <p>Предметы интерьера (люстры, картины, подсвечники, сувенирная продукция),</p>
                <p>Грузы, требующие вертикальной перевозки.</p>
            </td>
            <td width="332">
                <p>На евро поддоне в обрешетке: коробки из гофрокартона с амортизационными прокладками из пенопласта или
                    пупурчатой пленки не менее 3-х слоев или крафтпакетов по внутреннему периметру коробки и между
                    вложениями, если единиц товара более одной в коробке.</p>
            </td>
        </tr>
        <tr>
            <td width="312">
                <p>Одежда, текстиль, кожгалантерея.</p>
            </td>
            <td width="332">
                <p>Пакеты, коробки из гофрокартона без перегородок мешки.</p>
            </td>
        </tr>
        <tr>
            <td width="312">
                <p>Обувь, запасные части, канцелярские товары, бижутерия, детские игрушки, косметика (шампуни, крема и
                    так далее), бытовая химия, медикаменты в жидком виде.</p>
            </td>
            <td width="332">
                <p>Коробки из гофрокартона без перегородок, каждая товарная единица в индивидуальной упаковке,
                    либо обернута амортизационным материалом: крафтпакет, пенопласт/пупырчатая пленка не менее 3-х
                    слоев.</p>
            </td>
        </tr>
        <tr>
            <td width="312">
                <p>Растения, саженцы.</p>
            </td>
            <td width="332">
                <p>Коробки, обеспечивающие вертикальную перевозку.</p>
            </td>
        </tr>
        </tbody>
    </table>





    <hr />
    <h3 id="boxberry">Требования к упаковке отправлений Boxberry</h3>

     <h5> Вес и размеры </h5>
    <p>Наибольший вес одного места с учетом упаковки не должен превышать 15 кг. Сумма трех измерений (длин сторон)
        одного места не превышает 2,5 м, при этом любое измерение (длина наибольшей стороны) не превышает 1,2 м.</p>

     <h5> Вложения, запрещенные к отправке: </h5>
    <p>- огнестрельное, сигнальное, пневматическое, травматическое, газовое оружие, боеприпасы, холодное оружие (включая
        метательное), электрошоковые устройства и искровые разрядники, а также основные части огнестрельного оружия;</p>
    <p>- наркотические средства, психотропные, сильнодействующие, радиоактивные, взрывчатые, ядовитые, едкие,
        легковоспламеняющиеся и другие опасные вещества;</p>
    <p>- животные и растения;</p>
    <p>- ценные бумаги, денежные знаки Российской Федерации и иностранная валюта;</p>
    <p>- драгоценные металлы в любом виде и состоянии, природные драгоценные камни в сыром и обработанном виде, жемчуг и
        изделия из него;</p>
    <p>- предметы искусства, музейные экспонаты, ювелирные изделия и антикварные вещи;</p>
    <p>- скоропортящиеся продукты питания;</p>
    <p>- грузы, требующие особых условий хранения (уровень влажности, температурный режим);</p>
    <p>- предметы и вещества, которые по своему характеру или упаковке могут представлять опасность для сотрудников
        перевозчика, загрязнять или портить (повреждать) другие грузы, транспорт и оборудование перевозчика;</p>
    <p>- иные предметы/грузы, предусмотренные законодательством РФ.</p>

     <h5> Специальный режим доставки </h5>
    <p>Не принимаются к отправке отправления, вложения которых требует соблюдения специальных режимов транспортировки (в
        том числе: соблюдение температурного режима).</p>
    <p>Отдельному согласованию подлежат отправления, содержащие любые жидкие, аэрозольные и сыпучие фракции. Для таких
        отправлений обязательно наличие сертификата (или иного положенного документа) авиационной безопасности.</p>
    <p>Исполнитель не несет ответственность за сохранность таких отправлений во время транспортировки и сроки их
        доставки, в том числе, если они были приняты к перевозке ошибочно (вследствие незнания Исполнителем специальных
        требований, предъявляемых к подобным грузам и/или несообщения таких сведений Заказчиком).</p>

     <h5> Упаковка </h5>
    <p>Упаковка отправлений должна соответствовать характеру вложения, условиям доставки, исключать возможность
        повреждения вложения при обработке и транспортировке, доступа к нему без нарушения оболочки, порчи других
        отправлений.</p>
    <p>Упаковка должна быть сухой и чистой.</p>
    <p>Не допускается связывание двух коробок в одну скотчем, лентой или веревкой.</p>
    <p>При отправке бьющихся и колких вложений требуется обязательное использование воздушно-пузырьковой пленки,
        покрывающей каждое из бьющихся вложений. Пустоты между отдельными вложениями должны быть заполнены. Для такого
        типа вложений должна использоваться гофротара или иная тара, обеспечивающая жесткость упаковки.</p>
    <p>Основным требованием является целостность упаковки и обеспечение сохранности груза при перевозке от механических
        повреждений и хищений. Отправления без упаковки, в рваной и поврежденной упаковке не принимаются.</p>
    <p>В случае передачи Заказчиком отправлений в индивидуальной заводской упаковке Исполнитель, в ходе доставки не
        отвечает за целостность внешней части упаковки, а также за сохранность вложения. </p>






    <hr />
    <h3 id="russian-post">Требования к упаковке почтовых отправлений:</h3>
    <p>Упаковка должна быть прочной, исключать доступ к содержимому посылки.</p>
    <p>Содержимое посылки не должно перемещаться внутри упаковки &mdash; для этого свободное пространство в коробке
        можно заполнить полистиролом, опилками, стружкой, ватой.</p>
    <p>Хрупкие предметы нужно пересылать в твердой упаковке.</p>
    <p>Жидкости и текучие вещества необходимо пересылать в герметичных емкостях.</p>
    <p>Красящие вещества нужно пересылать в герметичных металлических емкостях, их нельзя посылать в полиэтиленовой или
        тканевой упаковке.</p>
    <p>Без дополнительной упаковки можно отправлять товары в фабричной упаковке или цельные небьющиеся предметы без
        острых выступов.</p>
    <p>На упаковке должно быть место для наклеивания адресного ярлыка размером не менее 10,5 &times; 14,8 см.</p>
    <p>На картонной упаковке не должно быть скотча или следов от него. Повторно использовать коробки Почты России для
        отправки посылок нельзя.</p>
    <p>Тканевая упаковка должна состоять из цельного или из нескольких однородных кусков светлой однотонной материи,
        сшитых внутренним швом. На тканевой упаковке адрес нужно написать, а не нашить. Перед упаковкой посылку
        необходимо защитить от намокания (для этого можно использовать полиэтиленовую пленку или другой непромокаемый
        материал).</p>

     <h5>Требования к упаковке почтовых отправлений по тарифу &laquo;Посылка Онлайн&raquo;: </h5>
    <p>Допустимые вложения: предмет культурно-бытового и иного назначения. Под почтовым отправлением в рамках
        настоящего Договора понимается отправление весом не более 5 кг. Размеры почтового отправления: </p>
    <p>-минимальный размер 240х160х10мм;</p>
    <p> - максимальный размер - сумма длины, высоты, ширины - не более 140 см, любая из сторон не должна превышать 60 см; </p>
    <p> - максимальная сумма объявленной стоимости 100 000 руб.</p>




    <hr />
    <h3 id="iml">Требования к упаковке отправлений IML</h3>
    <p>Все заказы, передаваемые на доставку, должны быть сформированы и промаркированы по следующим правилам:</p>
    <p>Заказ находится в индивидуальной упаковке, заклеенной фирменным скотчем или бумагой, пригодной для его перевозки
        автомобильным транспортом и исключающей доступ к заказу третьих лиц.</p>
    <p>В заказ необходимо вложить пакет сопроводительных документов, включающий в себя опись товаров по позиционно (ваша
        накладная/опись), в двух экземплярах.</p>
    <p>Вид упаковки должен отвечать особенностям грузов.</p>
    <p>Упаковка должна обеспечивать полную сохранность груза во время его транспортировки с учетом погрузо-разгрузочных
        работ.</p>
    <p>Для упаковки принципалу следует использовать материалы, соответствующие размерам, весу и уязвимости груза.
        Коробки, емкости и т.п. должны быть прочными, с амортизирующим материалом, для защиты от ударов, трения и
        тряски в пути (смятая бумага, ветошь, пузырчатая пленка и т.п.). Отдельные вложения, сложенные в коробки, пакеты
        и т.п., должны перекладываться разделителями &ndash; картонными листами, пенопластом и т.п. Предметы внутри
        коробок должны быть зафиксированы прокладочными материалами, во избежание переворачивания, и изолированы друг от
        друга.</p>
    <p>Хрупкий груз должен быть упакован таким образом, чтобы не было повреждений вложения при перевалке и мелких
        деформациях внешней упаковки (в результате тряски на борту воздушного судна).</p>
    <p>Жидкие и сыпучие вложения должны быть упакованы в герметичную тару, пригодную для перевозки таких веществ. Данные
        вещества не должны входить в <a href="http://iml.ru/legal">Перечень запрещенных к перевозке грузов</a>.</p>
    <p>Если в отправлении присутствует сыпучее вещество, жидкость или медикаменты, оно должно иметь паспорт безопасности
        либо другой документ, подтверждающий безопасность груза для авиаперевозки (например, письмо о безопасности
        данного груза для медикаментов).</p>
    <p>Опасные грузы к доставке Агентом не принимаются. Перечень опасных грузов размещен на сайте Агента по ссылке
        <a href="http://iml.ru/legal">Перечень запрещенных к перевозке грузов</a>.</p>
</div>
