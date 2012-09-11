<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="page">
		<html>
			<body>
				<h2><xsl:value-of select="@title" /></h2>
				<xsl:apply-templates />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="paragraph[@black]">
		<p style="background-color: #000; color: #FFF;">
			<xsl:apply-templates />
		</p>
	</xsl:template>

	<xsl:template match="paragraph">
		<p>
			<xsl:apply-templates />
		</p>
	</xsl:template>
	
	<xsl:template match="paragraph/paragraph">
		<p style="border: 1px solid #CCC;">
			<xsl:apply-templates />
		</p>
	</xsl:template>
	
</xsl:stylesheet>
