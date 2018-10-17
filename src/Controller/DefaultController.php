<?php

 /**
 * @file
 * Contains \Drupal\sneak_preview\Controller\DefaultController.
 */

namespace Drupal\sneak_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Default controller for the sneak_preview module.
 */
class DefaultController extends ControllerBase {

  public function sneak_preview_node($nid, $code) {
    $node = \Drupal::entityManager()->getStorage('node')->load($nid);

    // #FR-1062: get the latest revision, not just the current published one
    $revision_list = \Drupal::entityManager()->getStorage('node')->revisionIds($node);

    if (!empty($revision_list)) {
      ksort($revision_list);
      $latest = end($revision_list);

      // not the latest? reload the node
      if ($node->getRevisionId() != $latest) {
        $node = \Drupal::entityManager()->getStorage('node')->loadRevision($latest->vid);
      }
    }

    // Check if node is published, show normal
    if ($node->isPublished()) {
      drupal_goto('node/' . $node->nid);
    }

    // Check code and general permission
    if ($code != _sneak_preview_get_code($nid) || !\Drupal::currentUser()->hasPermission('allow sneak preview')) {
      drupal_access_denied();
      return;
    }

    // All OK, show message and unpublished node
    drupal_set_message(t('Please notice that you are looking at a sneak preview of an unpublished page on this site. You cannot be sure that all images are visible, links are active or that the page occurs exactly as if it was published.'), 'warning');
    return node_view($node);
  }

  public function sneak_preview_preview_access(Drupal\Core\Session\AccountInterface $account) {
    $code = FALSE;
    if (arg(0) == 'node' && is_numeric(arg(1))) {
      $node = \Drupal::entityManager()->getStorage('node')->load(arg(1));
      $code = _sneak_preview_get_code($node->nid);
    }
    // There has to be a code AND general node admin access to allow tab and page to be shown
    return $code && \Drupal::currentUser()->hasPermission('administer nodes');
  }

  public function sneak_preview_preview(\Drupal\node\NodeInterface $node) {
    $code = _sneak_preview_get_code($node->id());
    $roles = _sneak_preview_get_roles($node->getType());
    $note = isset($roles[0]) && $roles[0] == 'none' ?
      t('<br />Notice that no roles have permission to see sneak previews. Edit this in <a href="!url">the permission settings</a>.', [
      '!url' => \Drupal\Core\Url::fromRoute('user.admin_permissions')
      ]) :
      t('This link<br /><strong>!link</strong><br />will allow users with these roles !roles to see the unpublished node.', [
      '!link' => _sneak_preview_get_link($node->id()),
      '!roles' => theme_item_list([
        'items' => $roles,
        'title' => '',
        'type' => 'ul',
        'attributes' => [],
      ]),
    ]);
    $ret = $code ? $note : t('No sneak preview code available for this node');
    return $ret;
  }

  /**
   * Return if token is valid.
   */
  public function sneak_preview_api_getnid($token) {
    if ($token) {
      $query = db_select('sneak_preview', 's');
      $query->condition('s.code', $token, '=')->fields('s', array('nid'));
      $result = $query->execute();
      $nid = $result->fetchAssoc();
      if (!is_numeric($nid['nid'])) {
        return new JsonResponse('0');
      }
      else {
        return new JsonResponse('1');
      }
    }
  }
}
