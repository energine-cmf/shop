<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        version="1.0">
    <xsl:template match="component[@class='GoodsList' and @type='list']">
        <div class="goods_list clearfix">
            <xsl:apply-templates/>
        </div>
    </xsl:template>

    <xsl:template match="recordset[parent::component[@class='GoodsList' and @type='list']]">
        <xsl:for-each select="record">
            <xsl:variable name="URL">
                <xsl:value-of select="$BASE"/><xsl:value-of select="$LANG_ABBR"/><xsl:value-of
                    select="field[@name='smap_id']"/>view/<xsl:value-of
                    select="field[@name='goods_segment']"/>/</xsl:variable>
            <div class="goods_block">
                <div class="goods_image">
                    <a href="{$URL}">
                        <img src="{$RESIZER_URL}w200-h150/{field[@name='attachments']/recordset/record[1]/field[@name='file']}"
                             alt="{field[@name='attachments']/recordset/record[1]/field[@name='title']}"/>
                    </a>
                </div>
                <div class="goods_name">
                    <a href="{$URL}">
                        <xsl:value-of select="field[@name='goods_name']"/>
                    </a>
                </div>
                <div class="goods_producer">
                    <xsl:value-of select="field[@name='producer_id']/value"/>
                </div>
                <div class="goods_status available">
                    <xsl:value-of select="field[@name='sell_status_id']/value"/>
                </div>
                <div class="goods_price">
                    <xsl:value-of select="field[@name='goods_price']"/>
                </div>
                <div class="goods_controls clearfix">
                    <button type="button" class="buy_goods">BUY</button>
                    <a href="#" class="add_to_wishlist">ADD_TO_WISHLIST</a>
                </div>
            </div>
        </xsl:for-each>

    </xsl:template>

    <xsl:template match="component[@class='GoodsSort']">
        <xsl:variable name="GET"><xsl:if test="@get!=''">?<xsl:value-of select="@get"/></xsl:if></xsl:variable>
        <xsl:variable name="TEMPLATE" select="@template"/>
        <xsl:variable name="RECORDS" select="recordset/record"/>
        <xsl:for-each select="$RECORDS/field[@name='field']/options/option">
            <a href="{$BASE}{$LANG_ABBR}{$TEMPLATE}sort-{@id}-{$RECORDS/field[@name='dir']/options/option[not(@selected)]/@id}/{$GET}"><xsl:value-of select="."/><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text><xsl:value-of select="$RECORDS/field[@name='dir']/options/option[@selected]"/></a><xsl:if test="position()!=last()">
            <xsl:text disable-output-escaping="yes">&amp;nbsp;|&amp;nbsp;</xsl:text>
        </xsl:if>
        </xsl:for-each>
    </xsl:template>

    <xsl:template match="component[@class='GoodsFilter']">
        <form method="get" action="{@action}">
            <input type="hidden" name="componentAction" value="{@componentAction}" id="componentAction"/>
            <xsl:apply-templates/>
        </form>
    </xsl:template>

	<xsl:template match="component[@class='GoodsList' and @type='form']/recordset/record">
		<div class="goods_view clearfix">
			<div class="goods_image_block">
				<div class="goods_image">
					<img src="{field[@name='attachments']/recordset/record[1]/field[@name='file']}" alt="{field[@name='attachments']/recordset/record[1]/field[@name='name']}" />
				</div>
			</div>
			<div class="goods_info">
				<div class="goods_name">
					<xsl:value-of select="field[@name='goods_name']" />
				</div>
				<div class="goods_price">
					<xsl:value-of select="field[@name='goods_price']" />
				</div>
				<div class="goods_status available">
					<xsl:value-of select="field[@name='sell_status_id']/value" />
				</div>
				<div class="goods_buy">
					<button type="button" class="buy_goods">BUY</button>
				</div>
				<div class="goods_description">
					<xsl:value-of select="field[@name='goods_description_rtf']" disable-output-escaping="yes" />
				</div>
			</div>
			<div class="goods_features_block">
				<table class="goods_features">
					<thead>
						<tr>
							<th colspan="2">CHARACTERISTICS</th>
						</tr>
					</thead>
					<tbody>
						<xsl:for-each select="field[@name='features']/recordset/record">
							<xsl:if test="not(field[@name='feature_title'] = '')">
								<tr>
									<th><xsl:value-of select="field[@name='feature_title']" /></th>
									<td><xsl:value-of select="field[@name='feature_value']" /></td>
								</tr>
							</xsl:if>
						</xsl:for-each>
					</tbody>
				</table>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="component[@class='PageList' and @name='categoriesMenu']">
		<div class="categories_list clearfix">
			<xsl:for-each select="recordset/record">
				<div class="category">
					<a href="{field[@name='Segment']}">
						<div class="category_image">
							<div class="category_image_inner"> <!-- optional block for vertical align -->
								<img src="{field[@name='attachments']/recordset/record[1]/field[@name='file']}" alt="{field[@name='attachments']/recordset/record[1]/field[@name='name']}" />
							</div>
						</div>
						<div class="category_name">
							<xsl:value-of select="field[@name='Name']" />
						</div>
						<div class="category_description">
							<xsl:value-of select="field[@name='DescriptionRtf']" disable-output-escaping="yes" />
						</div>
					</a>
				</div>
			</xsl:for-each>
		</div>
	</xsl:template>

</xsl:stylesheet>