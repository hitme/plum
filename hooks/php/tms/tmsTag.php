<?php
// vim: set et sw=4 ts=4 sts=4 fdm=marker ffs=dos,unix fenc=gbk nobomb:
/**PHP.tpl
 * Author: ChangZhi <changzhi@taobao.com>
 * URL: 
 * Last Change:2011-04-23 
 * Version: 0.2
 * Desc: Taobao TMS Template Tags and API mock
 *
 * Change: [+]new feature  [*]improvement  [!]change  [x]bug fix
 *
 * [+] ����repeat��ǩ֧��
 * [+] ��ʼ���汾 2011-04-23
 */

/** ����tags�������� */
define ( '_TMS_TEXT', "string");
define ( '_TMS_LINK', "http://www.taobao.com");
define ( '_TMS_IMAGE', "http://img.f2e.taobao.net/img.png_");
/**
 * TMS ͨ��ǩ
 * ���˺�����Ϊ�����������ã�������ģ������ֱ��ʹ�ã�����TMS�������˴˺���Ŷ
 *
 * @since 0.1
 * @access private
 * @param string $args  '{"key":"va","key2":"va2"}'  Don't you think it is like JSON ? �r(������)�q
 * @param string $attributes  ĳЩ�����ǩ������ֵ
 * @require /includes/config.php
 * @require /includes/functions.php
 * @return array
 */
function tms_common ( $args , $attributes='' ) { //---------------------------------{{{

    $defaults = array (

        /**
         * ͨ��key
         * ���ݿ���ά���ĶԴ�������ά���������ݸ���ʱ���ܳ���������
         */
        'row' => 1,

        /*
         * ͨ��key
         * ȱʡ�ṩ����������
         */
        'defaultRow' => "1",

        /**
         * ͨ��key
         * ��Ʒ����
         */
        'text' => _TMS_TEXT,

        /**
         * ͨ��key
         *��Ʒ���ӵ�ַ
         */
        'href' => _TMS_LINK

    );

    /** ��jsonתΪ���飬������utf-8����,���֮��Ҳ��utf-8 */
    $args = tb_json_decode($args);


    /*
    if(!array_key_exists('name',$args)||!array_key_exists('title',$args)||!array_key_exists('group',$args)) {
        echo "��@#������ǩȱ��name��title��group���ԣ��ϴ���TMS�����";
    }
     */
    /** �ϲ�����ֵ */
    $defaults = tms_parse_args ( $attributes , $defaults );
    $r = tms_parse_args ( $args ,$defaults );
    $myFileds = array();

    /** ����_tms_custom�Զ���fields */
    if ( isset($r['fields']) AND $r['fields'] !='' ) {
        $pos = strpos( $r['fields'],'(') || strpos($r['fields'],')');
        if($pos) {
            echo "��@#��%��custom ��ǩ�������溬�зǷ��ַ���(��)���ϴ���TMS�����";
        }

        $r['fields'] = explode(',',$r['fields']);

        $r2 = array();
        $mathImg = array();

        /**
         * ȡfields�����һ��͵�����ֱ���Ϊ��ֵ
         * ����ڶ����ڱ�����û���õģ������ϴ���tms�ϻᱻ����
         */
        foreach ( $r['fields'] as $index=>&$key ) {
			$imgmatch = preg_match('/\[([\dx]*)]\:img/',$key,$math);
            $key = explode( ':',$key );
            $r2 +=  array( $key[0] => $key[2] );
            $myFileds[] = $key[0];
            if( $imgmatch ) {
              $mathImg[$index][$key[2]] = $math[1];
            }
        }


        /** �Զ����fields���������� */
		$index = 0;
        foreach ( $r2 as &$key2 ) {
            switch ( $key2 ) {
            case "boolean":
                $key2 = false;
                //$key2 = "_TMS_BOOLEAN";
                break;
            case "string":
                $key2 = _TMS_TEXT;
                break;
            case "multilString":
                $key2 = _TMS_TEXT;
                break;
            case "href":
                $key2 = _TMS_LINK;
                break;
            case "img":
                if (isset($mathImg[$index])){
                    $key2 = _TMS_IMAGE .$mathImg[$index][$key2].'.jpg';
                }
                break;
            case "date":
                $key2 = date("YmdHis");
                break;
            case "email":
                $key2 = "email";
                break;
            default:
                $key2 = _TMS_TEXT;
            }
            $index++;
        }
        /** ���¸�ֵ */
        unset($r['fields']);
        $r +=$r2;

    }


    $filter = array('row', 'defaultRow', 'title', 'name', 'group');
    $r3 = array();
    for ( $i = 0; $i < $r['defaultRow']; $i++ ) {
        //array_push( $r3,$r );
        $rets = array();
        foreach($r as $key => $val) {
            if (preg_match('/^http:\/\/img\.f2e\.taobao\.net(.*)/i', $val)) {
                $val = preg_replace('/\.jpg(.*)/i', '.jpg?t=' . md5(microtime()), $val);
            }

            if (!in_array($key, $filter) || in_array($key, $myFileds)){
                $rets[$key] = $val;
            }
        }
        $r3[] = $rets;
    }

    return $r3;

}//--------------------------------------------------------------}}}

/**
 * TMS ����
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *      text:ά�����ı�����
 */
function _tms_text ( $args='' ) {
    return _tms_common( $args );
}

/**
 * TMS ��������
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *      text:ά�����ı�����
 *      href:���ӵ�ַ
 */
function _tms_textLink ( $args='' ) {
    return _tms_common( $args );
}

/**
 * TMS ͼƬ
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *      text:ά�����ı�����
 *      img��ͼƬ��ַ
 */
function _tms_image ( $args='' ) {

    $argsNew = tb_json_decode($args);
    $size = preg_match('/\[([\dx]*)]/', $argsNew->title, $match);

    $attributes = array (
        /**
         * ͨ��key
         * ��ƷͼƬ��ַ
         */
        'img' => _TMS_IMAGE . $match[1] . '.jpg',
    );

    return _tms_common( $args ,$attributes );
}

/**
 * TMS ͼƬ����
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *      text:ά�����ı�����
 *      href:���ӵ�ַ
 *      img��ͼƬ��ַ
 */
function _tms_imageLink ( $args='' ) {

    $argsNew = tb_json_decode($args);
    $size = preg_match('/\[([\dx]*)]/', $argsNew->title, $match);

    $attributes = array (
        /**
         * ͨ��key
         * ��ƷͼƬ��ַ
         */
        'img' => _TMS_IMAGE . $match[1] . '.jpg',
    );


    return _tms_common( $args ,$attributes );
}

/**
 * TMS ��Ʒ ����Ϊ�ֶ���д
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *      text:ά�����ı�����
 *      href:���ӵ�ַ
 *      img��ͼƬ��ַ
 *  	price����Ʒ�۸�
 *  	point������
 *  	saleNum:��������
 *  	extras��״̬
 */
function _tms_product ( $args='' ) {

    $attributes = array (

        /**
         * ��Ʒ�۸�
         */
        'price' => '1999.00',

        /**
         * ����
         */
        'point' => '10',

        /**
         * ��������
         */
        'saleNum' => '10',

        /**
         * ״̬
         */
        'extras' => '',
        /**
         * ͨ��key
         * ��ƷͼƬ��ַ
         */
        'img' => _TMS_IMAGE,
    );

    return _tms_common( $args , $attributes );
}

/**
 * TMS ��Ʒ��ǩ �����ǰ���ѯ������ȡ
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *      text:ά�����ı�����
 *      href:���ӵ�ַ
 *      img��ͼƬ��ַ
 *  	price����Ʒ�۸�
 *  	point������
 *  	saleNum:��������
 *  	extras��״̬
 */
function _tms_productList ( $args='' ) {

    $attributes = array (
        /**
         * ͨ��key
         * ��ƷͼƬ��ַ
         */
        'img' => _TMS_IMAGE,
    );


    return _tms_product( $args ,$attributes );
}

/**
 * TMS �Զ���
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *  	fields:�Զ���������ֶ�����,��ʽ:key1:����1:type1,key2:����2:type2
 */

function _tms_custom ( $args='' ) {

    $json = json_decode( iconv( 'gbk','utf-8',$args ) , true );
    if(!array_key_exists('row',$json)||!array_key_exists('defaultRow',$json)) {
        echo "��@#���� _tms_custom��ǩȱʧrow��defaultRow���ԣ��ϴ���TMS�����";
        break;
    }

    $attributes = array (

        /**
         * �Զ����ֶ�
         */
        'fields' => ''

    );

    return _tms_common ( $args , $attributes);
}

function _tms_autoExtract($args = '') {
    return _tms_custom($args);
}
/**
 * TMS �����б�
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 id:��Ѷ���
 created������ʱ��
 modified������ʱ��
 publishedUrl������url
 title1������1
 title2������2
 title3������3
 authorId���������к�
 author��������
 authorUrl������URL
 articleCatalogId����Ŀ���к�
 articleType������
 tag����ǩ
 tagLink�������ӵı�ǩ
 priority��Ȩ��
 priority2�����ȼ�
 source����Դ
 sourceUrl����ԴURL
 articleAbstract�����ժҪ
 articleBody������
 image1��ͼ1 1:1
 image2��ͼ2 250x165
 image3��ͼ3 190��150
 image4��ͼ4 110X90
 templateId������ģ�����к�
 url��url
 positionTag��λ�ñ�ǩ
 articlePath���������Ĵ洢·��
 *  	
 */
function _tms_articleList ( $args='' ) {

    $attributes = array (

        /**
         * ��Ѷ���
         */
        'id' => '',

        /**
         * ����ʱ��
         */
        'created' => '',
        'img' => _TMS_IMAGE,

        /**
         * ����ʱ��
         */
        'modified' => '',

        /**
         * ����url
         */
        'publishedUrl' => '',

        /**
         * ����1
         */
        'title1' => '',

        /**
         * ����2
         */
        'title2' => '',

        /**
         * ����3
         */
        'title3' => '',

        /**
         * �������к�
         */
        'authorId' => '',

        /**
         * ������
         */
        'author' => '',

        /**
         * ����URL
         */
        'authorUrl' => '',

        /**
         * ��Ŀ���к�
         */
        'articleCatalogId' => '',

        /**
         * ����
         */
        'articleType' => '',

        /**
         * ��ǩ 
         */
        'tag' => '',

        /**
         * �����ӵı�ǩ
         */
        'tagLink' => '',

        /**
         * Ȩ��
         */
        'priority' => '',

        /**
         * ���ȼ�
         */
        'priority2' => '',

        /**
         * ��Դ
         */
        'source' => '',

        /**
         * ��ԴURL
         */
        'sourceUrl' => '',

        /**
         * ���ժҪ
         */
        'articleAbstract' => '',

        /**
         * ����
         */
        'articleBody' => '',

        /**
         * ͼ1 1:1
         */
        'image1' => '',

        /**
         * ͼ2 250x165
         */
        'image2' => '',

        /*saleNum*
         * ͼ3 190��150
         */
        'image3' => '',

        /**
         * ͼ4 110X90
         */
        'image4' => '',

        /**
         * ����ģ�����к�
         */
        'templateId' => '',

        /**
         * url
         */
        'url' => '',

        /**
         * λ�ñ�ǩ
         */
        'positionTag' => '',

        /**
         * �������Ĵ洢·��
         */
        'articlePath' => ''

    );

    return _tms_common ( $args , $attributes);
}

/**
 * TMS ��Ŀlist
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 text����Ŀ����
 url����Ŀ����
 *      
 */
function _tms_categoryList ( $args='' ) {

    $attributes = array (

        'img' => _TMS_IMAGE,
        /**
         * url
         */
        'url' => _TMS_LINK

    );

    return _tms_common ( $args , $attributes );
}

/**
 * TMS ��Ŀ����
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 *     	defaultValue��pid:vid
 */
function _tms_catePropertype ( $args='' ) {

    $attributes = array (

        /**
         * pid:vid
         */
        'defaultValue' => ''

    );

    return _tms_common ( $args , $attributes );
}

/**
 * TMS �ڱ�
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 text:��������
 img:ͼƬ����
 href:��������
 menuList:���̼�Ŀ��
 address:���̵�ַ
 picTelno:�绰
 p4cTelno:��ϵ�绰
 perPrice:�˾�����
 comebackpercent:��ͷ��
 userImpress:����ӡ��
 koubei:�ڱ�ָ��
 recommendFood:�Ƽ���
 isCoupon:�Ƿ����Ż�:0/1
 isactivity:�Ƿ��л:0/1
 discount���ڱ����ۿ�
 */
function _tms_koubei ( $args='' ) {

    $attributes = array (

        /**
         * ���̼�Ŀ��
         */
        'menuList' => '',

        'img' => _TMS_IMAGE,
        /**
         * ���̵�ַ
         */
        'address' => '',

        /**
         * �绰
         */
        'picTelno' => '',

        /**
         * ��ϵ�绰
         */
        'p4cTelno' => '',

        /**
         * �˾�����
         */
        'perPrice' => '',

        /**
         * ��ͷ��
         */
        'comebackpercent' => '',

        /**
         * ����ӡ��
         */
        'userImpress' => '',

        /**
         * �ڱ�ָ��
         */
        'koubei' => '',

        /**
         * �Ƽ���
         */
        'recommendFood' => '',

        /**
         * �Ƿ����Ż�:0/1
         */
        'isCoupon' => '',

        /**
         * �Ƿ��л:0/1
         */
        'isactivity' => '',

        /**
         * �ڱ����ۿ�
         */
        'discount' => '',

    );

    return _tms_common ( $args , $attributes );
}

/**
 * TMS ����
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 title:ͼƬ����
 fullCoverPicPath������Ĭ��ͼƬ
 posterAccessPath����������
 userNick������������
 shortTitle��ͼƬ�̱���
 *      
 */
function _tms_posterList ( $args='' ) {

    $attributes = array (

        /**
         * _tms_posterList ר��
         * ����Ĭ��ͼƬ
         */
        'fullCoverPicPath' => '',

        'img' => _TMS_IMAGE,

        /**
         * _tms_posterList ר��
         * ��������
         */
        'posterAccessPath' => '',

        /**
         * _tms_posterList ר��
         * ����������
         */
        'userNick' => '',

        /**
         * _tms_posterList ר��
         * ͼƬ�̱���
         */
        'shortTitle' => '',

    );

    return _tms_common ( $args , $attributes );
}

/**
 * TMS ���а�
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 *      name
 *      title
 *      group
 *      row
 *      defaultRow
 title: �񵥱���(��ѭ���޹�)
 catName����Ŀ����(��ѭ���޹�)
 allHref������������
 toprankid����ID(��ѭ���޹�)
 href������Url
 imgͼƬurl
 rankPubPeriod��չ������
 rankId��ID
 objectId��supid
 idx������ָ��
 idxRank������������
 idxLast������������
 idxRankChg�����仯
 idxChgָ��仯 ��עָ������
 idxDownRankָ���½�����
 idxChgRateָ��仯���� ��ע��������
 idxUpRankָ����������
 idxUpRateRankָ��������������
 idxDownRateRankָ���½��������� 
 addedQuantity�����۳�
 id���
 dateʱ��
 itemStaus
 spuId��Ʒ SPUID ��ƷID
 productName
 category��Ʒ������ĿID
 productNwPrice��Ʒ�۸�
 productGroupFlag��Ʒ������Ʒ �崮 
 productStartDate��Ʒ����ʱ��
 productPriceChgWeek��Ʒ�۸�һ�� �仯
 productSellerNum��Ʒ����������
 alipayTradeNumW��ALIPAY�ܱ���
 alipayTradeNumIdxW���ܱ� ������ ALIPAY����
 *      
 */
function _tms_ranking ( $args='' ) {

    $attributes = array (

        /**
         * ��Ŀ����(��ѭ���޹�)
         */
        'catName' => '',

        /**
         * ����������
         */
        'allHref' => '',

        /**
         * ��ID(��ѭ���޹�)
         */
        'toprankid' => '',

        /**
         * ��չ������
         */
        'rankPubPeriod' => '',
        'img' => _TMS_IMAGE,

        /**
         * ��ID
         */
        'rankId' => '',

        /**
         * supid
         */
        'objectId' => '',

        /**
         * ������ָ��
         */
        'idx' => '',

        /**
         * ������������
         */
        'idxRank' => '',

        /**
         * ������������
         */
        'idxLast' => '',

        /**
         * �����仯
         */
        'idxRankChg' => '',

        /**
         * ָ��仯 ��עָ������
         */
        'idxChg' => '',

        /**
         * ָ���½�����
         */
        'idxDownRank' => '',

        /**
         * ָ����������
         */
        'idxUpRank' => '',

        /**
         * ָ��������������
         */
        'idxUpRateRank' => '',

        /**
         * ָ���½��������� 
         */
        'idxDownRateRank' => '',

        /**
         * �����۳�
         */
        'addedQuantity' => '',

        'itemStaus' => '',

        /**
         * ��Ʒ SPUID ��ƷID
         */
        'spuId' => '',

        /**
         * _tms_ranking ר��
         * 
         */
        'productName' => '',

        /**
         * ��Ʒ������ĿID
         */
        'category' => '',

        /**
         * ��Ʒ�۸�
         */
        'productNwPrice' => '',

        /**
         * ��Ʒ������Ʒ �崮 
         */
        'productGroupFlag' => '',

        /**
         * ��Ʒ����ʱ��
         */
        'productStartDate' => '',

        /**
         * ��Ʒ�۸�һ�� �仯
         */
        'productPriceChgWeek' => '',

        /**
         * ��Ʒ����������
         */
        'productSellerNum' => '',

        /**
         * ALIPAY�ܱ���
         */
        'alipayTradeNumW' => '',

        /**
         * �ܱ� ������ ALIPAY����
         */
        'alipayTradeNumIdxW' => ''

    );

    return _tms_common ( $args , $attributes );
}

/**
 * TMS ����
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 */

function _tms_more ( $args='' ) {
    return _tms_common ( $args );
}

/**
 * TMS �������ñ��ش��ھ����ñ��ز����ھ���������
 * ��������������ϵ�Ҳ��php����������html
 * ����Ҫȥ��tms�������ļ�ֻ���Ժ���������ڿ��ӿ�
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 */

function _tms_subArea ( $args='' ) {
    /*
    $pdir = SAIL_PAGE . '/view'.$args;
    global $spk_name;
    $self = HTDOC . $spk_name .'/'. $args;
    $self_mod = HTDOC . $spk_name .'/modules/'. $args;
    $tms = new TMS_TAG;
    if(is_file($pdir)){
        $tms->repeatReplace($pdir);
    }elseif(is_file($args)){
        $tms->repeatReplace($args);
    }elseif(is_file($self)){
        $tms->repeatReplace($self);
    }else{
		require_once(SAIL_INC.'Snoopy.class.php');
		//�޸�Ϊsnoopy ��ȡ start
		$remote = new Snoopy();
		$remote->fetch( "http://www.taobao.com/go" . $args );

		return eval('{?>'.$remote->results.'<?}');

		//�޸�Ϊsnoopy ��ȡ end
        //return include ( "http://www.taobao.com/go" . $args );

    }
    */
    return;
}

/**
 * TMS �����˵�
 *
 * @since 0.1
 * @access publish
 * @param $args string
 * @key:
 */

function _tms_nav ( $args='' ) {

    $attributes = array (
        'childList' => _TMS_TEXT
    );

    return _tms_common ( $args , $attributes );
}

/**
 * TMS ģ���ǩ
 *
 * @since 0.1
 * @access publish
 * @param $args string
 */
function _tms_module_begin() {
    return;
}

function _tms_module_end() {
    return;
}

/**
 * TMS repeat��ǩ
 *
 * @since 0.1
 * @access publish
 * @param $args string
 */
function _tms_repeat_begin( $arg='' ) {
    return;
}

function _tms_repeat_end() {
    return;
}
?>
