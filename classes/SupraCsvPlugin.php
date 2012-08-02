<?
class SupraCsvPlugin {

    private $plugin_name = 'supra-csv-parser';

    public function getPluginDirUrl() {
        return WP_PLUGIN_URL . '/' . $this->plugin_name .'/';
    }

    public function getCsvDir() {
        return WP_PLUGIN_DIR . '/' . $this->plugin_name .'/'. 'csv' . '/';
    }

    public function getCsvDirUrl() {
        return $this->getPluginDirUrl() . 'csv' . '/';
    }
}
