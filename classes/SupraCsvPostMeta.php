<?php 
namespace SupraCsvFree;

class SupraCsvPostMeta {

    private function buildInputRow($metainfo=array(),$num) {

            if(is_array($metainfo)) extract($metainfo);

            $input_row = null;

            $input_row .= '<tr id="pm_info'.$num.'" class="pm_info">';

            if(isset($checked))           
               $checked = ($checked)?'checked="checked"':'';
            else 
              $checked = null;
 
            $input_row .= '<td><input type="checkbox" name="use_metakey[]" id="use_metakey" '.$checked.' value="'.$num.'" /></td>';
            $input_row .= '<td><input type="text" name="meta_key[]" id="meta_key" value="'.$meta_key.'" size="40" maxlength="40" /></td>';
            $input_row .= '<td><input type="text" name="displayname[]" id="displayname" value="'.$displayname.'" size="40" maxlength="40" /></td>';
            $input_row .= '</tr>';

            return $input_row;
    }

    public function getFormContents($postmetas,$suggestions) {

        $form = <<<EOF
  <table>
    <tr id="labeling">
      <td>Enabled</td>
      <td>Post Meta Key</td>
      <td>Display Name</td>
    </tr>
EOF;

        //Debug::show($postmetas);

        if(is_array($postmetas) && count($postmetas)) {
            foreach($postmetas['meta_key'] as $i=>$meta_key) {
                $displayname = $postmetas['displayname'][$i];
                $checked = in_array($i,(array)$postmetas['use_metakey']);
                $form .= $this->buildInputRow(compact('meta_key','displayname','checked'),$i);
            }
        }

        $suggestion_keys = $this->diffSuggestionsFromStoredPostmeta($suggestions,$postmetas);

        $i = 0;

        foreach($suggestions as $suggestion) {

            if(in_array($suggestion->meta_key,$suggestion_keys)) {
                $i++;
                $displayname = $suggestion->meta_key;
                $meta_key = $suggestion->meta_key;
                $form .= $this->buildInputRow(compact('displayname','meta_key'),$i);
             }
         }

        if(!count($suggestions) && !count($postmetas))
            $form .= $this->buildInputRow(null,0);
 

        $form .= <<<EOF
    <tr id="pm_buttons">
        <td colspan="2">
            <button id="add_pmr">Add Post Meta</button>
            &nbsp; &nbsp;
            <button id="rem_pmr">Remove Post Meta</button>
        </td>
    </tr>
  </table>
EOF;

        return $form;

    }

    public function diffSuggestionsFromStoredPostmeta($suggestions,$postmetas) {

        $s_keys = array();
        $m_keys = array();

        foreach((array)$suggestions as $suggestion) {

          $s_keys[] = $suggestion->meta_key;

        }

        foreach((array)$postmetas['meta_key'] as $postmeta) {

          $m_keys[] = $postmeta;

        }

        $select_keys = array_diff($s_keys,$m_keys);
 
        return $select_keys;
     }


    public function getSuggestions($post_type) {
        global $wpdb;

        $sql = "SELECT pm.meta_key, pm.`meta_value`
                FROM ".$wpdb->prefix."postmeta AS pm
                LEFT JOIN ".$wpdb->prefix."posts AS p ON p.ID = pm.post_id
                WHERE p.post_type =  '$post_type'
                GROUP BY pm.meta_key";

        return $wpdb->get_results($sql,OBJECT);
    }
}

