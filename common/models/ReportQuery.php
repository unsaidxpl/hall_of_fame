<?php
/**
 * Created by PhpStorm.
 * User: ekz
 * Date: 9/10/17
 * Time: 9:26 PM
 */

namespace common\models;

use yii\db\ActiveQuery;


class ReportQuery extends ActiveQuery
{
    /**
     * Default scope
     * Exclude deleted events
     */
    public function init()
    {
        $this->andWhere(['is', 'report.deleted_at', null]);
        parent::init();
    }

    /**
     * Filter published reports
     * @return $this
     */
    public function published() {
        return $this->andWhere(['report.status' => 'published']);
    }

    /**
     * Filter pending reports
     * @return $this
     */
    public function pending() {
        return $this->andWhere(['report.status' => 'pending']);
    }

    /**
     * Get events created by specific user
     * @param $id
     * @return $this
     */
    public function byUserId($id)
    {
        return $this->andWhere(['user_id' => $id]);
    }
}
