import os
import re
import unittest

from selenium import webdriver

from config import PAGE


def mask_bg(content):
    return re.sub(r'bg\d+.png', r'bg[masked].png', content)


def mask_map_folder(content):
    content = re.sub(r'maps/[0-9a-f]{10}/', r'maps/[masked_folder]/', content)
    return re.sub(r'(for|id|name)="([a-zA-Z]+)[a-f0-9]{10}"', r'\1="\2[masked]"', content)


def tcn():
    with open('testdata/TradeCartNothing.rms', 'r') as f:
        return f.read()


def get_private_id_from_url(url):
    m = re.search(r'edit/([0-9a-f]+)', url)
    return m.group(1)


def get_public_id_from_url(url):
    return url.split('/')[-1]


class FullCycleTest(unittest.TestCase):

    def setUp(self):
        self.driver = webdriver.Firefox()
        self.failures = []

    def tearDown(self):
        self.driver.close()
        self.assertEqual([], self.failures)
        # reset_config_and_submissions()

    def test_check_validation_files(self):
        self.driver.get(PAGE)
        self.assertIn("snippets.aoe2map.net", self.driver.title)
        self.compareWithValidationFile('index')
        self.click_page_link('a-new-snippet', 'Snippet Title')
        self.compareWithValidationFile('new')

    def test_full_cycle(self):
        self.driver.get(PAGE)
        self.assertIn("snippets.aoe2map.net", self.driver.title)
        self.assertNotIn("Public sharing URL", self.driver.page_source)
        self.click_page_link('a-new-snippet', 'Snippet Title')
        trade_cart_nothing = tcn()
        title = 'TradeCartNothing'
        self.fill_fields({
            'titleInput': title,
            'rmsInput': trade_cart_nothing,
        })
        self.click_page_link('btn-save', 'Public sharing URL')
        self.assert_input_value('titleInput', title)
        self.assert_input_value('rmsInput', trade_cart_nothing)
        self.assert_edit_url_is_current_url()
        public_url = self.get_public_url()
        public_id = get_public_id_from_url(public_url)
        self.driver.get(PAGE + public_id)
        self.compareWithValidationFile('public_page', [lambda x: x.replace(public_id, '[masked]')])

    def test_empty_title_gets_replaced_with_public_id(self):

        # GIVEN
        self.driver.get(PAGE)
        self.assertIn("snippets.aoe2map.net", self.driver.title)
        self.assertNotIn("Public sharing URL", self.driver.page_source)
        self.click_page_link('a-new-snippet', 'Snippet Title')
        trade_cart_nothing = tcn()
        title = 'nonempty'

        # WHEN
        self.fill_fields({
            'titleInput': '',
            'rmsInput': trade_cart_nothing,
        })

        # THEN
        self.click_page_link('btn-save', 'Public sharing URL')
        public_url = self.get_public_url()
        public_id = get_public_id_from_url(public_url)
        self.assert_input_value('titleInput', public_id)

        # WHEN
        self.fill_fields({
            'titleInput': title,
        })

        # THEN
        self.click_page_link('btn-save', 'Public sharing URL')
        self.assert_input_value('titleInput', title)

        # WHEN
        self.fill_fields({
            'titleInput': '',
        })

        # THEN
        self.click_page_link('btn-save', 'Public sharing URL')
        public_url = self.get_public_url()
        public_id = get_public_id_from_url(public_url)
        self.assert_input_value('titleInput', public_id)

    def test_empty_content_yields_an_error(self):

        # GIVEN
        self.driver.get(PAGE)
        self.assertIn("snippets.aoe2map.net", self.driver.title)
        self.assertNotIn("Public sharing URL", self.driver.page_source)
        self.click_page_link('a-new-snippet', 'Snippet Title')
        title = 'Empty content'
        content = 'not empty anymore'

        # WHEN
        self.fill_fields({
            'titleInput': title,
            'rmsInput': '',
        })

        # THEN
        self.click_page_link('btn-save', 'Your snippet has no content. You should fix that.')
        self.assertNotIn("Public sharing URL", self.driver.page_source)
        self.assert_input_value('titleInput', title)

        # WHEN
        self.fill_fields({
            'rmsInput': content,
        })

        # THEN
        self.click_page_link('btn-save', 'Public sharing URL')
        self.assert_input_value('titleInput', title)

        # WHEN
        self.fill_fields({
            'rmsInput': '',
        })

        # THEN
        self.click_page_link('btn-save', 'Your snippet has no content. You should fix that.')
        self.assertIn("Public sharing URL", self.driver.page_source)

    def assert_input_value(self, element_id, value):
        title_input = self.driver.find_element_by_id(element_id)
        self.assertEqual(title_input.get_attribute('value'), value)

    def fill_fields(self, content):
        for key, value in content.items():
            self.fill_field(key, value)

    def fill_field(self, element_id, content):
        field = self.driver.find_element_by_id(element_id)
        field.clear()
        field.send_keys(content)

    def click_page_link(self, element_id, content):
        link = self.driver.find_element_by_id(element_id)
        self.scroll_to(link)
        link.click()
        self.assertIn(content, self.driver.page_source)

    def scroll_to(self, link):
        self.driver.execute_script("arguments[0].scrollIntoView();", link)

    def compareWithValidationFile(self, suffix="", masking=None):
        if masking is None:
            masking = []
        masking.append(mask_bg)
        masking.append(mask_map_folder)

        output = self.driver.page_source
        for func in masking:
            output = func(output)
        validation = "=== new file ===\n{}".format(output)
        filename = "{}_{}.html".format(self._testMethodName, suffix)
        with open(os.path.join("output", filename), "w") as f:
            print(output, file=f)

        if not os.path.isfile(os.path.join("validation", filename)):
            with open(os.path.join("validation", filename), "w") as f:
                print(validation, file=f)
        else:
            with open(os.path.join("validation", filename), "r") as f:
                validation = f.read()

        if output.strip() != validation.strip():
            self.failures.append("{} does not match {}!".format(
                os.path.join("output", filename),
                os.path.join("validation", filename)
            ))

    def get_public_url(self):
        return self.driver.find_element_by_id('publicUrlInput').get_attribute('value')

    def assert_edit_url_is_current_url(self):
        private_url_from_input = self.driver.find_element_by_id('privateUrlInput').get_attribute('value')
        current_edit_url = self.driver.current_url
        self.assertEqual(get_private_id_from_url(private_url_from_input), get_private_id_from_url(current_edit_url))


if __name__ == "__main__":
    unittest.main()
