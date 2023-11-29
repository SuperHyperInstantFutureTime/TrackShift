<?php
namespace SHIFT\TrackShift\Content;

use Gt\Dom\Element;
use Gt\Dom\NodeList;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

readonly class ContentRepository {
	public function __construct(
		private string $directoryPath,
	) {}

	public function bindNodeList(NodeList $contentNodes):void {
		foreach($contentNodes as $contentNode) {
			$this->bindNode($contentNode);
		}
	}

	public function bindNode(Element $element):void {
		$contentName = $element->dataset->get("content");
		$markdownPath = "$this->directoryPath/$contentName.md";
		$markdown = file_get_contents($markdownPath);
		$html = $this->markdownToHtml($markdown);
		$element->innerHTML = $html;
	}

	private function markdownToHtml(string $markdown):string {
		$environment = new Environment([
			"html_input" => "allow"
		]);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new AttributesExtension());

		$converter = new MarkdownConverter($environment);
		return $converter->convert($markdown);
	}

}
