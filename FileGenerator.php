<?php 
/**
* Takes posts from PostGrabber.php and generates all files for necessary for the epub. Then creates epub file.
*/
class FileGenerator
{
	private $collection; //collection object. $title=string title of collection $posts = array of post data
	private $directory; //title of the epub that will be generated
	
	/**
	* @param $collection: an object that contains a $title and an array of $posts
	*
	* Also creates a name for $directory that will become the epub
	*/

	function __construct($collection)
	{
		$this->collection=$collection;

		$regpattern = array("/January/",
							"/February/",
							"/March/",
							"/April/",
							"/May/",
							"/June/",
							"/July/",
							"/August/",
							"/September/",
							"/October/",
							"/November/",
							"/December/",
							"/Sunday/",
							"/Monday/",
							"/Tuesday/",
							"/Wednesday/",
							"/Thursday/",
							"/Friday/",
							"/Saturday/",
							"/ /","/\//","/,/");
		$regreplace = array("01-",
							"02-",
							"03-",
							"04-",
							"05-",
							"06-",
							"07-",
							"08-",
							"09-",
							"10-",
							"11-",
							"12-",
							"--",
							"--",
							"--",
							"--",
							"--",
							"--",
							"--"
						);

		$dirtitle = preg_replace($regpattern,$regreplace, $this->collection->title);

		$dirtitle = substr_replace($dirtitle, "-", -4,0);

		$this->directory="$dirtitle";
	}


	/**
	* Creates an array containing the posts formatted in epub-ready xhtml
	*
	* @return an array with xhtml filenames as keys and xhtml formatted posts as values.
	* 	Each key-value pair represents one post. 
	*/
	function gen_text_files(){

		$collection = $this->collection;

		//load in template for post html files
		$templatefh = fopen("EpubTemplate/OEBPS/Text/posttemplate.xhtml", "r");
		$template = fread($templatefh, filesize("EpubTemplate/OEBPS/Text/posttemplate.xhtml"));
		fclose($templatefh);




		//places the body of each posts into the template, adds it to the array
		$output = array();
		foreach ($this->collection->posts as $key => $value) {
			
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$postbody = $value["body"];

			$strpattern = array("posttitle",
								"postauthor",
								"postbody",
							);

			$strreplace = array ($posttitle,
								 $postauthor,
								 $postbody
							);

			$outstr = str_replace($strpattern, $strreplace, $template);

			$outfilepath = "OEBPS/Text/$posttitle--$postauthor.xhtml";

			
			$output[$outfilepath]=$outstr;
		}

		return $output;
	}

	/**
	* @return the contents of the title.xhtml file
	*/
	function gen_title_file(){

		//load in template
		$templatefh=fopen("EpubTemplate/OEBPS/Text/title.xhtml","r");
		$filetext=fread($templatefh, filesize("EpubTemplate/OEBPS/Text/title.xhtml"));
		fclose($templatefh);

		//
		$filetext = str_replace("collectiontitle", $this->collection->title, $filetext);
		return $filetext;
	}

	/**
	* Generates the text for the origanizational files: content.opf, toc.xhtml, toc.ncx
	*
	* @return an array containg the file titles as keys and the contents as values
	*/
	function gen_org_files(){
		
		$posts = $this->collection->posts;

		//template for each post entry in toc.xhtml
		$postHtmlTemplate = '<a href="../Text/postlink.xhtml">posttitleauthor</a><br/><br/>';

		//templates for each post entry in content.opf
		$postOpfTemplate = ' <item id="posttitleauthor" href="Text/postlink.xhtml" media-type="application/xhtml+xml" />';
		$opfSpineTemplate = '<itemref idref="posttitleauthor" />';

		//template for each tox.ncx entry
		$postNcxTemplate = '
		<navPoint class="chapter" id="postlink" playOrder="playordernum">
			<navLabel>
				<text>posttitleauthor</text>
			</navLabel>
			<content src="Text/postlink.xhtml"/>
		</navPoint>';

		$htmllinks = "";
		$opflinks = "";
		$opfspine = "";
		$ncxlinks = "";

		$searches = array("postlink","posttitleauthor","playordernum");
		$count = 3;

		//for each post, adds entries to each file
		foreach ($posts as $value){
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$replace = array("$posttitle--$postauthor","$posttitle by $postauthor","$count");

			$posthtml = str_replace($searches, $replace, $postHtmlTemplate);
			$htmllinks.="$posthtml\n\n";

			$postopf = str_replace($searches, $replace, $postOpfTemplate);
			$postopfspine = str_replace($searches, $replace, $opfSpineTemplate);
			$opflinks.="\t\t$postopf\n";
			$opfspine .= "\t\t$postopfspine\n";

			$postncx = str_replace($searches, $replace, $postNcxTemplate);
			$ncxlinks.="\t\t$postncx\n\n";
			$count++;
		}


		//generate contents of tox.xhtml

		$tocFileTemplate = fopen("EpubTemplate/OEBPS/Text/toc.xhtml","r");
		$tocTemplateText = fread($tocFileTemplate, filesize("EpubTemplate/OEBPS/Text/toc.xhtml"));
		fclose($tocFileTemplate);

		$searches = array("collectiontitle","postlinks");
		$replace = array($this->collection->title,$htmllinks);

		
		$htmlfilename = "OEBPS/Text/toc.xhtml";
		$htmloutput = str_replace($searches, $replace, $tocTemplateText);

		




		// generate contents of content.opf

		$opfFileTemplate = fopen("EpubTemplate/OEBPS/content.opf","r");
		$opfTemplateText = fread($opfFileTemplate, filesize("EpubTemplate/OEBPS/content.opf"));
		fclose($opfFileTemplate);

		$searches = array("postlinks","postspine","collectiontitle");
		$replace = array($opflinks,$opfspine,$this->collection->title);

		$opfoutput = str_replace($searches, $replace, $opfTemplateText);

		$opffilename = "OEBPS/content.opf";	

		// generate contents of toc.ncx

		$tocFileTemplate = fopen("EpubTemplate/OEBPS/toc.ncx","r");
		$tocTemplateText = fread($tocFileTemplate, filesize("EpubTemplate/OEBPS/toc.ncx"));
		fclose($tocFileTemplate);

		$ncxoutput = str_replace("postlinks", $ncxlinks, $tocTemplateText);

		$ncxfilename = "/OEBPS/toc.ncx";


		//create output array from all generate files

		$output = array($htmlfilename=>$htmloutput,
					 $opffilename=>$opfoutput,
					 $ncxfilename=>$ncxoutput
				);

		return $output;
	}

	/**
	* Creates the epub file. Calls each of the gen_x_file() functions and creates files from their outputs.
	* Then adds files to the epub with the appropriate file structure.
	*
	* @return a string path to the generated file
	*/
	function gen_epub(){

		$pathToEpub = "$this->directory.epub";

		//epub files are just zip files with a .epub file extention

		$zip = new ZipArchive();
		$zip->open($pathToEpub, ZipArchive::CREATE);

		//adds mimetype file from template folder. mimetype must be added first
		$zip->addfile("EpubTemplate/mimetype","mimetype");

		$zip->close();
		$zip->open("$this->directory.epub");

		//create the directory structure
		$dirs = array("OEBPS",
					  "OEBPS/Images",
					  "OEBPS/Styles",
					  "OEBPS/Text",
					  "META-INF"
					);
		

		foreach ($dirs as $value) {
			$zip->addEmptyDir($value);
		}

		//adds text posts to zip
		$posts = $this->gen_text_files();
		foreach ($posts as $key => $value) {
			$zip->addFromString($key,$value);
		}

		//add title.xhtml file
		$titlefiletext = $this->gen_title_file();
		$zip->addFromString("OEBPS/Text/title.xhtml",$titlefiletext);

		//add content.opf, toc.xhtml, toc.ncx
		$tocfiles = $this->gen_org_files();
		foreach ($tocfiles as $key => $value) {
			$zip->addFromString($key,$value);
		}

		//add additional files from EpubTemplate folder
		
		$zip->addfile("EpubTemplate/META-INF/container.xml","META-INF/container.xml");
		$zip->addfile("EpubTemplate/OEBPS/Images/cover.jpg","OEBPS/Images/cover.jpg");
		$zip->addfile("EpubTemplate/OEBPS/Text/bookcover.xhtml","OEBPS/Text/bookcover.xhtml");
		$zip->addfile("EpubTemplate/OEBPS/Images/backcover.jpg","OEBPS/Images/backcover.jpg");
		$zip->addfile("EpubTemplate/OEBPS/Text/backcover.xhtml","OEBPS/Text/backcover.xhtml");
		$zip->close();

		return $pathToEpub;

	}

	

}


 ?>